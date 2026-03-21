<?php

	use Discord\Parts\Channel\Message;
	use Discord\Parts\Embed\Embed;
	use Discord\Builders\MessageBuilder;
	use React\Http\Browser;

	class Footy extends AbstractCommand {

		// ── constants ──────────────────────────────────────────────────────────

		private const AFL_LOGO_URL  = 'https://www.afl.com.au/resources/v5.45.13/i/elements/afl-logo.png';
		private const MATCHES_BASE  = 'https://aflapi.afl.com.au/afl/v2/matches';
		private const WAGERING_URL  = 'https://api.afl.com.au/cfs/afl/wagering?application=Web';
		private const TZ_MELBOURNE  = 'Australia/Melbourne';

		// Status strings returned by the matches API
		private const STATUS_CONCLUDED  = 'CONCLUDED';
		private const STATUS_LIVE       = 'LIVE';

		// Filter modes driven by command arguments
		private const FILTER_ALL        = 'all';
		private const FILTER_CONCLUDED  = 'concluded';
		private const FILTER_LIVE       = 'live';
		private const FILTER_UPCOMING   = 'upcoming';

		// Set to true to send a wagering debug report alongside the fixture embed
		private const WAGERING_DEBUG    = false;

		// Token endpoint – POST with no body to receive a short-lived x-media-mis-token
		private const TOKEN_URL         = 'https://api.afl.com.au/cfs/afl/WMCTok';

		// Live match detail endpoint – append providerId (e.g. CD_M20260140203)
		private const MATCH_ITEM_BASE   = 'https://api.afl.com.au/cfs/afl/matchItem/';

		// ── AbstractCommand implementation ─────────────────────────────────────

		public function getName(): string {
			return 'Footy';
		}

		public function getDesc(): string {
			return 'Shows the current AFL week\'s fixture, scores and wagering odds.';
		}

		public function getPattern(): string {
			return '/\b(footy|football|afl)\b/i';
		}

		// ── entry point ────────────────────────────────────────────────────────

		public function execute(Message $message, string $args, array $matches): void {
			
			if ($message->channel->id != 1352902587837583370) { return; }

			$filter    = $this->parseFilter($args);
			$thursday  = $this->getPreviousThursday();
			$wednesday = (clone $thursday)->modify('+6 days');

			$matchesUrl = self::MATCHES_BASE
				. '?status=L,U,C'
				. '&startDate=' . $thursday->format('Y-m-d')
				. '&endDate='   . $wednesday->format('Y-m-d')
				. '&competitionId=1';

			$browser = new Browser($this->discord->getLoop());

			// Shared headers that mimic the browser's CORS fingerprint
			$corsHeaders = [
				'Accept'            => '*/*',
				'Accept-Language'   => 'en-AU,en;q=0.7',
				'Cache-Control'     => 'no-cache',
				'DNT'               => '1',
				'Origin'            => 'https://www.afl.com.au',
				'Pragma'            => 'no-cache',
				'Referer'           => 'https://www.afl.com.au/',
				'Sec-Fetch-Dest'    => 'empty',
				'Sec-Fetch-Mode'    => 'cors',
				'Sec-Fetch-Site'    => 'same-site',
				'Sec-GPC'           => '1',
				'User-Agent'        => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36',
			];

			// ── Step 1: fetch auth token ───────────────────────────────────────
			// POST to WMCTok with no body to receive a short-lived x-media-mis-token.
			// Once resolved, fire matches + wagering concurrently in step 2.
			$browser
				->post(self::TOKEN_URL, $corsHeaders, '')
				->then(
					function ($r) use ($browser, $corsHeaders, $matchesUrl, $message, $thursday, $filter): void {

						$tokenData = json_decode((string) $r->getBody(), true);
						$token     = $tokenData['token'] ?? null;

						if ($token === null) {
							$message->reply('❌ Failed to retrieve AFL wagering token. Cannot load odds.');
							return;
						}

						// ── Step 2: fire matches + wagering concurrently ───────────
						$matchesPromise = $browser
							->get($matchesUrl, array_merge($corsHeaders, [
								'Accept' => 'application/json',
							]))
							->then(
								fn ($r) => json_decode((string) $r->getBody(), true),
								fn ($e) => null
							);

						// Wagering request captures full response envelope for debug
						$wageringPromise = $browser
							->get(self::WAGERING_URL, array_merge($corsHeaders, [
								'x-media-mis-token' => $token,
							]))
							->then(
								function ($r) {
									$statusCode = $r->getStatusCode();
									$rawBody    = (string) $r->getBody();
									$decoded    = json_decode($rawBody, true);

									return [
										'data'        => $decoded,
										'statusCode'  => $statusCode,
										'bodyExcerpt' => mb_substr($rawBody, 0, 300),
										'jsonError'   => $decoded === null ? json_last_error_msg() : null,
									];
								},
								function (\Throwable $e) {
									return [
										'data'        => null,
										'statusCode'  => null,
										'bodyExcerpt' => null,
										'jsonError'   => null,
										'httpError'   => $e->getMessage(),
									];
								}
							);

						// ── Step 3: fetch live scores, then build embed ───────────
						\React\Promise\all([$matchesPromise, $wageringPromise])->then(
							function (array $results) use ($message, $thursday, $browser, $corsHeaders, $token, $filter): void {

								[$matchesData, $wageringResult] = $results;

								$wageringData = $wageringResult['data'] ?? null;

								// ── wagering debug report ──────────────────────────────
								if (self::WAGERING_DEBUG) {
									$debugLines = ['```', '── Wagering Debug ──'];

									if (isset($wageringResult['httpError'])) {
										$debugLines[] = 'HTTP error : ' . $wageringResult['httpError'];
									} else {
										$debugLines[] = 'HTTP status : ' . ($wageringResult['statusCode'] ?? 'unknown');
										$debugLines[] = 'JSON error  : ' . ($wageringResult['jsonError'] ?? 'none');
										$debugLines[] = 'Decoded OK  : ' . ($wageringData !== null ? 'yes' : 'no');

										if ($wageringData !== null) {
											$books      = $wageringData['competition']['books'] ?? null;
											$bookCount  = is_array($books) ? count($books) : 'key missing';
											$matchBooks = is_array($books)
												? count(array_filter($books, fn ($b) => $b['matchId'] !== null))
												: 0;
											$debugLines[] = 'competition.books count : ' . $bookCount;
											$debugLines[] = 'Books with matchId      : ' . $matchBooks;
											if (is_array($books) && !empty($books)) {
												$first        = $books[0];
												$debugLines[] = 'First book matchId : ' . ($first['matchId'] ?? 'null');
												$debugLines[] = 'First book name    : ' . ($first['name']    ?? '?');
											}
										} else {
											$debugLines[] = 'Body (first 300 chars):';
											$debugLines[] = $wageringResult['bodyExcerpt'] ?? '(empty)';
										}
									}

									$debugLines[] = '```';
									$message->channel->sendMessage(
										MessageBuilder::new()->setContent(implode("\n", $debugLines))
									);
								}

								if (empty($matchesData['matches'])) {
									$message->reply('❌ Could not retrieve AFL fixture data. Please try again shortly.');
									return;
								}

								// ── Step 4: fetch live match detail for any LIVE games ─
								// The main matches API does not include scores for live games;
								// we must hit matchItem/{providerId} individually for each.
								// Skip entirely if the current filter excludes live matches.
								$livePromises = [];
								if ($filter === self::FILTER_ALL || $filter === self::FILTER_LIVE) {
									foreach ($matchesData['matches'] as $match) {
										if (($match['status'] ?? '') !== self::STATUS_LIVE) {
											continue;
										}
										$pid = $match['providerId'] ?? null;
										if ($pid === null) {
											continue;
										}
										$livePromises[$pid] = $browser
											->get(self::MATCH_ITEM_BASE . $pid, array_merge($corsHeaders, [
												'x-media-mis-token' => $token,
											]))
											->then(
												fn ($r) => json_decode((string) $r->getBody(), true),
												fn ($e) => null   // live score failure is non-fatal
											);
									}
								}

								// If no live games to fetch, build the embed immediately
								if (empty($livePromises)) {
									$embed = $this->buildEmbed($matchesData, $wageringData, $thursday, [], $filter);
									$message->channel->sendMessage(MessageBuilder::new()->addEmbed($embed));
									return;
								}

								\React\Promise\all($livePromises)->then(
									function (array $liveScores) use ($message, $thursday, $matchesData, $wageringData, $filter): void {
										$embed = $this->buildEmbed($matchesData, $wageringData, $thursday, $liveScores, $filter);
										$message->channel->sendMessage(MessageBuilder::new()->addEmbed($embed));
									},
									function (\Throwable $e) use ($message): void {
										$message->reply('❌ Failed to fetch live match scores: ' . $e->getMessage());
									}
								);
							},
							function (\Throwable $e) use ($message): void {
								$message->reply('❌ An unexpected error occurred: ' . $e->getMessage());
							}
						);
					},
					function (\Throwable $e) use ($message): void {
						$message->reply('❌ Failed to reach AFL token endpoint: ' . $e->getMessage());
					}
				);
		}

		// ── embed builder ──────────────────────────────────────────────────────

		private function buildEmbed(array $matchesData, ?array $wageringData, \DateTime $thursday, array $liveScores, string $filter): Embed {

			$tz  = new \DateTimeZone(self::TZ_MELBOURNE);
			$now = new \DateTime('now', $tz);

			// Index wagering books by match providerId (e.g. "CD_M20260140202")
			$wagering = $this->indexWagering($wageringData);

			$wednesday  = (clone $thursday)->modify('+6 days')->setTime(23, 59, 59);
			$rawMatches = $matchesData['matches'] ?? [];

			$roundNum  = null;
			$byDay     = [];
			$byeTeams  = [];
			$byesSeen  = false;

			foreach ($rawMatches as $match) {
				$utc = $match['utcStartTime'] ?? null;
				if (!$utc) {
					continue;
				}

				$matchTime = (new \DateTime($utc, new \DateTimeZone('UTC')))->setTimezone($tz);

				// Constrain to the Thursday–Wednesday window
				if ($matchTime < $thursday || $matchTime > $wednesday) {
					continue;
				}

				// Apply status filter
				$status = $match['status'] ?? '';
				$include = match ($filter) {
					self::FILTER_CONCLUDED => $status === self::STATUS_CONCLUDED,
					self::FILTER_LIVE      => $status === self::STATUS_LIVE,
					self::FILTER_UPCOMING  => !in_array($status, [self::STATUS_CONCLUDED, self::STATUS_LIVE], true),
					default                => true, // FILTER_ALL
				};
				if (!$include) {
					continue;
				}

				// Capture round info and byes from the first qualifying match.
				// The byes array is identical across all matches in a round,
				// so we only need to read it once.
				if ($roundNum === null) {
					$roundNum = $match['round']['roundNumber'] ?? null;

					foreach ($match['round']['byes'] ?? [] as $byeTeam) {
						$name = $byeTeam['name'] ?? null;
						if ($name && !in_array($name, $byeTeams, true)) {
							$byeTeams[] = $name;
						}
					}
					$byesSeen = true;
				}

				// Some matches in the API window may belong to an adjacent round;
				// collect their byes too if we haven't yet (e.g. a split round).
				if (!$byesSeen) {
					foreach ($match['round']['byes'] ?? [] as $byeTeam) {
						$name = $byeTeam['name'] ?? null;
						if ($name && !in_array($name, $byeTeams, true)) {
							$byeTeams[] = $name;
						}
					}
				}

				$byDay[$matchTime->format('Y-m-d')][] = [
					'match' => $match,
					'time'  => $matchTime,
				];
			}

			// Sort days chronologically, then matches within each day by kickoff time
			ksort($byDay);
			foreach ($byDay as &$dayMatches) {
				usort($dayMatches, fn ($a, $b) => $a['time'] <=> $b['time']);
			}
			unset($dayMatches);

			// ── score worm (live filter only) ──────────────────────────────────
			// Generate a quickchart.io stepped line chart URL for the first live
			// match that has scoring event data. The image is attached to the embed.
			$wormUrl = null;
			if ($filter === self::FILTER_LIVE) {
				foreach ($byDay as $dayMatches) {
					foreach ($dayMatches as $entry) {
						$m   = $entry['match'];
						$pid = $m['providerId'] ?? null;
						if ($pid === null || !isset($liveScores[$pid])) {
							continue;
						}
						$wormUrl = $this->generateScoreWormUrl(
							$liveScores[$pid],
							$m['home']['team']['name'] ?? 'Home',
							$m['away']['team']['name'] ?? 'Away'
						);
						if ($wormUrl !== null) {
							break 2;
						}
					}
				}
			}

			// ── assemble embed ─────────────────────────────────────────────────
			$embed = new Embed($this->discord);
			$embed->setColor(getenv('COLOUR'));
			$embed->setThumbnail(self::AFL_LOGO_URL);
			$roundLabel = $roundNum !== null ? "Round {$roundNum}" : 'Fixture';
			$filterLabel = match ($filter) {
				self::FILTER_CONCLUDED => 'Finished',
				self::FILTER_LIVE      => 'Live',
				self::FILTER_UPCOMING  => 'Upcoming',
				default                => 'Summary',
			};
			$embed->setAuthor("AFL {$roundLabel} - {$filterLabel}", self::AFL_LOGO_URL);

			$thursday->setTime(0, 0, 0);
			$weekEnd   = (clone $thursday)->modify('+6 days');
			$weekRange = $thursday->format('D jS M') . ' – ' . $weekEnd->format('D jS M Y');
			$embed->setDescription("**{$weekRange}**");

			// ── one field per matchday ─────────────────────────────────────────
			foreach ($byDay as $dayKey => $dayMatches) {

				$dayDate  = new \DateTime($dayKey, $tz);
				$dayLabel = '__' . $dayDate->format('l jS F') . '__';
				$lines    = [];

				foreach ($dayMatches as $entry) {
					$matchLines = $this->renderMatch($entry['match'], $entry['time'], $wagering, $liveScores);
					array_push($lines, ...$matchLines);
					$lines[] = '';
				}

				// Remove trailing blank separator
				if (!empty($lines) && end($lines) === '') {
					array_pop($lines);
				}

				// Leading blank line gives the date heading clear air above the first fixture
				$fieldValue = "\n" . implode("\n", $lines);

				// Enforce Discord's 1 024-char field value limit
				if (mb_strlen($fieldValue) > 1024) {
					$fieldValue = mb_substr($fieldValue, 0, 1020) . '…';
				}

				$embed->addFieldValues($dayLabel, $fieldValue ?: '—', false);
			}

			// ── byes ───────────────────────────────────────────────────────────
			// Byes come directly from round.byes[] in the API – no guessing needed.
			if (!empty($byeTeams)) {
				sort($byeTeams);
				$embed->addFieldValues('🛋️ Bye This Week', implode(', ', $byeTeams), false);
			}

			if ($wormUrl !== null) {
				$embed->setImage($wormUrl);
			}

			$embed->setFooter('Data: afl.com.au  •  ' . $now->format('D jS M Y, g:ia') . ' AEST');

			return $embed;
		}

		// ── match renderer ─────────────────────────────────────────────────────

		/**
		 * Returns an array of display lines for a single fixture.
		 *
		 * Confirmed matches API paths:
		 *   $match['home']['team']['name']          — team display name
		 *   $match['home']['team']['providerId']    — e.g. "CD_T10" (links to wagering)
		 *   $match['home']['score']['goals']        — only present on CONCLUDED/IN_PROGRESS
		 *   $match['home']['score']['behinds']
		 *   $match['home']['score']['totalScore']
		 *   $match['away']                          — same structure as home
		 *   $match['providerId']                    — e.g. "CD_M20260140202" (links to wagering)
		 *   $match['status']                        — CONCLUDED | IN_PROGRESS | SCHEDULED | UNCONFIRMED_TEAMS
		 *   $match['venue']['name']                 — e.g. "MCG"
		 *   $match['venue']['location']             — e.g. "Melbourne"
		 *   $match['utcStartTime']                  — ISO-8601 UTC string
		 *
		 * @param  array<string, mixed>  $match
		 * @param  \DateTime             $matchTime  Already converted to Melbourne time
		 * @param  array<string, array>  $wagering   Keyed by match providerId
		 * @return string[]
		 */
		private function renderMatch(array $match, \DateTime $matchTime, array $wagering, array $liveScores): array {

			$homeTeam     = $match['home']['team']['name']       ?? 'Home';
			$awayTeam     = $match['away']['team']['name']       ?? 'Away';
			$homeProvider = $match['home']['team']['providerId'] ?? null;
			$awayProvider = $match['away']['team']['providerId'] ?? null;
			$venue        = $match['venue']['name']              ?? 'TBC';
			$city         = $match['venue']['location']          ?? '';
			$status       = $match['status']                     ?? 'SCHEDULED';
			$providerId   = $match['providerId']                 ?? null;
			$venueStr     = $city ? "{$venue}, {$city}" : $venue;

			$lines   = [];
			$lines[] = "**{$homeTeam} vs {$awayTeam}**";

			if ($status === self::STATUS_CONCLUDED) {
				// Completed – spoilered score on the left, venue on the right
				$hScore  = $this->formatScore($match['home']['score'] ?? []);
				$aScore  = $this->formatScore($match['away']['score'] ?? []);
				$lines[] = "🏁 ||{$hScore} – {$aScore}||  ·  📍 *{$venueStr}*";

			} elseif ($status === self::STATUS_LIVE) {
				// Live – fetch scores and clock from the matchItem endpoint.
				// $liveScores is keyed by match providerId.
				$liveData = $liveScores[$providerId] ?? null;

				if ($liveData !== null) {
					$hMatchScore = $liveData['score']['homeTeamScore']['matchScore'] ?? [];
					$aMatchScore = $liveData['score']['awayTeamScore']['matchScore'] ?? [];
					$hScore      = $this->formatScore($hMatchScore);
					$aScore      = $this->formatScore($aMatchScore);

					// Find the currently active period (periodCompleted === false)
					$periods       = $liveData['score']['matchClock']['periods'] ?? [];
					$activePeriod  = null;
					foreach ($periods as $period) {
						if (($period['periodCompleted'] ?? true) === false) {
							$activePeriod = $period;
							break;
						}
					}

					$quarter = $activePeriod['periodNumber'] ?? null;
					$secs    = $activePeriod !== null ? (int) $activePeriod['periodSeconds'] : null;
					$mins    = $secs !== null ? (int) floor($secs / 60) : null;
					$secPart = $secs !== null ? str_pad($secs % 60, 2, '0', STR_PAD_LEFT) : null;
					$qStr    = '';
					if ($quarter !== null && $mins !== null) {
						$qStr = " (Q{$quarter} {$mins}:{$secPart})";
					} elseif ($quarter !== null) {
						$qStr = " (Q{$quarter})";
					}

				} else {
					// matchItem fetch failed – show live indicator without score
					$hScore = $aScore = '?';
					$qStr   = '';
				}

				$lines[] = "🔴 **LIVE{$qStr}**  ||{$hScore} – {$aScore}||  ·  📍 *{$venueStr}*";

			} else {
				// Upcoming (SCHEDULED / UNCONFIRMED_TEAMS) – time + countdown on the left, venue on the right
				$timestamp = $matchTime->getTimestamp();
				$timeStr   = $matchTime->format('g:ia');
				$lines[]   = "🕐 {$timeStr}  ·  <t:{$timestamp}:R>  ·  📍 *{$venueStr}*";
			}

			// ── wagering ───────────────────────────────────────────────────────
			if ($providerId !== null && isset($wagering[$providerId])) {
				$wageringLines = $this->renderWagering(
					$wagering[$providerId],
					$homeProvider,
					$awayProvider
				);
				array_push($lines, ...$wageringLines);
			}

			return $lines;
		}

		// ── wagering renderer ──────────────────────────────────────────────────

		/**
		 * Returns wagering lines for one match.
		 *
		 * Confirmed wagering API paths:
		 *   $book['propositions'][]            — always exactly 2 entries for match books
		 *   $proposition['clubId']             — e.g. "CD_T10" (matches team.providerId)
		 *   $proposition['name']               — team display name
		 *   $proposition['h2h']                — head-to-head decimal odds (float|null)
		 *   $proposition['line']               — line market decimal odds (float|null)
		 *   $proposition['handicap']           — handicap points; negative = favourite (float|null)
		 *   $proposition['sortOrder']          — 1 = home team, 2 = away team (fallback)
		 *
		 * @param  array<string, mixed>  $book
		 * @param  string|null           $homeProvider  e.g. "CD_T10"
		 * @param  string|null           $awayProvider  e.g. "CD_T140"
		 * @return string[]
		 */
		private function renderWagering(array $book, ?string $homeProvider, ?string $awayProvider): array {

			$propositions = $book['propositions'] ?? [];
			if (empty($propositions)) {
				return [];
			}

			// Match each proposition to home/away by clubId.
			// Only consider propositions that carry H2H odds – this filters out
			// any season-long premiership market entries that may share clubIds.
			$homeProp = null;
			$awayProp = null;

			foreach ($propositions as $prop) {
				if ($prop['h2h'] === null) {
					continue;
				}
				$clubId = $prop['clubId'] ?? null;

				if ($clubId === $homeProvider) {
					$homeProp = $prop;
				} elseif ($clubId === $awayProvider) {
					$awayProp = $prop;
				}
			}

			// Fallback: use sortOrder if clubId matching failed
			if ($homeProp === null || $awayProp === null) {
				$sorted = $propositions;
				usort($sorted, fn ($a, $b) => ($a['sortOrder'] ?? 99) <=> ($b['sortOrder'] ?? 99));
				$homeProp ??= $sorted[0] ?? null;
				$awayProp ??= $sorted[1] ?? null;
			}

			if (!$homeProp || !$awayProp) {
				return [];
			}

			$lines = [];

			// Head-to-head
			$hH2H = $homeProp['h2h'] ?? null;
			$aH2H = $awayProp['h2h'] ?? null;

			if ($hH2H !== null && $aH2H !== null) {
				$lines[] = sprintf(
					'💰 H2H: %s $%.2f / %s $%.2f',
					$homeProp['name'] ?? 'Home', $hH2H,
					$awayProp['name'] ?? 'Away', $aH2H
				);
			}

			// Handicap line market
			$hLine = $homeProp['line']     ?? null;
			$aLine = $awayProp['line']     ?? null;
			$hHcap = $homeProp['handicap'] ?? null;
			$aHcap = $awayProp['handicap'] ?? null;

			if ($hLine !== null && $aLine !== null && $hHcap !== null && $aHcap !== null) {
				$hHcapStr = ($hHcap > 0 ? '+' : '') . $hHcap;
				$aHcapStr = ($aHcap > 0 ? '+' : '') . $aHcap;
				$lines[]  = "📊 Line: {$hHcapStr} / {$aHcapStr}";
			}

			return $lines;
		}

		// ── helpers ────────────────────────────────────────────────────────────

		/**
		 * Parses the argument string from the command into a filter constant.
		 *
		 * Accepted values (case-insensitive):
		 *   (none)                              → FILTER_ALL
		 *   f, fi, fin, fini, finis, finish,
		 *   finishe, finished                   → FILTER_CONCLUDED
		 *   live                                → FILTER_LIVE
		 *   upcoming, next                      → FILTER_UPCOMING
		 */
		private function parseFilter(string $args): string {

			$arg = strtolower(trim($args));

			if ($arg === '') {
				return self::FILTER_ALL;
			}

			if (preg_match('/^f(i(n(i(s(h(e(d)?)?)?)?)?)?)?$/', $arg)) {
				return self::FILTER_CONCLUDED;
			}

			if ($arg === 'live') {
				return self::FILTER_LIVE;
			}

			if ($arg === 'upcoming' || $arg === 'next') {
				return self::FILTER_UPCOMING;
			}

			// Unrecognised argument – fall back to showing all
			return self::FILTER_ALL;
		}

		/**
		 * Builds a quickchart.io stepped line chart URL representing the score worm.
		 *
		 * Data source: $liveData['score']['scoreWorm']['scoringEvents'][]
		 *   $event['aggregateHomeScore'] — cumulative home score after this event
		 *   $event['aggregateAwayScore'] — cumulative away score after this event
		 *
		 * Both series start at 0 (before the first scoring event) and step up
		 * with each event. The x-axis is suppressed; only the score trajectory matters.
		 *
		 * Returns null if there are no scoring events yet.
		 */
		private function generateScoreWormUrl(array $liveData, string $homeTeam, string $awayTeam): ?string {

			$events = $liveData['score']['scoreWorm']['scoringEvents'] ?? [];
			if (empty($events)) {
				return null;
			}

			// Seed both series at 0-0 before the first event
			$homeData = [0];
			$awayData = [0];
			$labels   = [0];

			foreach ($events as $i => $event) {
				$homeData[] = (int) ($event['aggregateHomeScore'] ?? 0);
				$awayData[] = (int) ($event['aggregateAwayScore'] ?? 0);
				$labels[]   = $i + 1;
			}

			$config = [
				'type' => 'line',
				'data' => [
					'labels'   => $labels,
					'datasets' => [
						[
							'label'           => $homeTeam,
							'data'            => $homeData,
							'borderColor'     => '#1a73e8',
							'backgroundColor' => 'transparent',
							'stepped'         => 'before',
							'pointRadius'     => 0,
							'borderWidth'     => 2,
						],
						[
							'label'           => $awayTeam,
							'data'            => $awayData,
							'borderColor'     => '#e53935',
							'backgroundColor' => 'transparent',
							'stepped'         => 'before',
							'pointRadius'     => 0,
							'borderWidth'     => 2,
						],
					],
				],
				'options' => [
					'legend' => [
						'labels' => ['fontColor' => '#ffffff'],
					],
					'scales' => [
						'xAxes' => [[
							'display' => false,
						]],
						'yAxes' => [[
							'ticks' => [
								'beginAtZero' => true,
								'fontColor'   => '#ffffff',
							],
							'gridLines' => [
								'color' => 'rgba(255,255,255,0.15)',
							],
						]],
					],
				],
			];

			return 'https://quickchart.io/chart?c=' . urlencode(json_encode($config)) . '&w=500&h=200&bkg=%2336393f';
		}

		/**
		 * If today is Thursday, today's midnight is returned.
		 */
		private function getPreviousThursday(): \DateTime {

			$tz  = new \DateTimeZone(self::TZ_MELBOURNE);
			$now = new \DateTime('now', $tz);

			// ISO-8601 day-of-week: 1 = Mon … 4 = Thu … 7 = Sun
			$dow      = (int) $now->format('N');
			$daysBack = match (true) {
				$dow === 4 => 0,        // today is Thursday
				$dow < 4   => $dow + 3, // Mon → 4, Tue → 5, Wed → 6 days back
				default    => $dow - 4, // Fri → 1, Sat → 2, Sun → 3 days back
			};

			$dt = clone $now;
			$dt->modify("-{$daysBack} days");
			$dt->setTime(0, 0, 0);

			return $dt;
		}

		/**
		 * Formats a score array as "G.B **Total**" — e.g. "14.15 **99**"
		 *
		 * Confirmed score fields: goals, behinds, totalScore
		 *
		 * @param  array<string, int> $score
		 */
		private function formatScore(array $score): string {

			$goals   = (int) ($score['goals']      ?? 0);
			$behinds = (int) ($score['behinds']    ?? 0);
			$total   = (int) ($score['totalScore'] ?? ($goals * 6 + $behinds));

			return "{$goals}.{$behinds} **{$total}**";
		}

		/**
		 * Builds a providerId-keyed index of wagering books.
		 *
		 * Confirmed wagering structure:
		 *   $wageringData['competition']['books'][]
		 *   $book['matchId'] — e.g. "CD_M20260140202", or null for season-long markets
		 *
		 * The link between the two APIs:
		 *   wagering  $book['matchId']   ===   matches  $match['providerId']
		 *
		 * @param  array<string, mixed>|null $wageringData
		 * @return array<string, array<string, mixed>>
		 */
		private function indexWagering(?array $wageringData): array {

			if ($wageringData === null) {
				return [];
			}

			$books   = $wageringData['competition']['books'] ?? [];
			$indexed = [];

			foreach ($books as $book) {
				$matchId = $book['matchId'] ?? null;

				// Skip season-long markets (premiership winner etc.) where matchId is null
				if ($matchId === null) {
					continue;
				}

				$indexed[$matchId] = $book;
			}

			return $indexed;
		}

	}
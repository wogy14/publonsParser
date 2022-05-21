<?php

namespace components;

class Parser {

	/**
	 * Returns array with sessions
	 *
	 * @return array
	 */
	public static function getSessions(): array {
		$conn = DB::getConnection();
		$sql = 'SELECT * FROM `session`';
		$rows = [];

		foreach ($conn->query($sql) as $row) {
			$rows[] = $row;
		}

		return $rows;
	}

	/**
	 * Returns info about Publications by researcherID
	 *
	 * @param $publonsResearcherID
	 * @return array
	 */
	public static function getPublications($publonsResearcherID): array {
		$urlForPublications = "https://publons.com/api/profile/publication/$publonsResearcherID/";

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $urlForPublications);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$publications = json_decode(curl_exec($ch));
		curl_close($ch);

		return $publications;
	}

	/**
	 * Method to start parsing
	 *
	 * @param string $institution
	 * @param int $page
	 */
	public static function parse(string $institution, int $page = 1) {
		$sessions = self::getSessions();
		$parsedAuthors = 0;
		$parsedPublications = 0;

		$keyNumber = 0;
		$urlForResearches = "https://publons.com/api/v2/academic/?institution=" . urlencode($institution) . "&page=$page";

		do {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $urlForResearches);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'Authorization: Token ' . $sessions[$keyNumber]['token'],
				'Content-Type: application/json'
			]);
			$tempResearchers = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			$tempResearchers = json_decode($tempResearchers);

			foreach ($tempResearchers->results as $researcher) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://publons.com/api/stats/individual/{$researcher->ids->id}/");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$researcherData = json_decode(curl_exec($ch));
				curl_close($ch);

				$authorId = DB::insertOrUpdate('author', [
					'pid'                => (int)($researcher->ids->id),
					'publications_count' => (int)($researcher?->publications?->count ?? 0),
					'rid'                => htmlspecialchars($researcher?->ids?->rid ?? ''),
					'full_name'          => htmlspecialchars($researcher?->publishing_name ?? ''),
					'h_index'            => (float)($researcherData?->hIndex ?? 0),
					'times_cited'        => (int)($researcherData?->timesCited ?? 0),
					'orcid'              => htmlspecialchars($researcher?->ids?->orcid ?? ''),
					'photo'              => htmlspecialchars($researcher?->photo),
					'citations_per_year' => $researcherData?->citationsPerYear ? json_encode($researcherData->citationsPerYear) : null,
					'publons_updated_at' => htmlspecialchars($researcher?->datetime_records_last_updated ?? ''),
					'session_id'         => $sessions[$keyNumber]['id'],
					'created_at'         => date(DATE_ATOM)
				], ['pid']);

				sleep(1);
				$publications = self::getPublications($researcher->ids->id);

				foreach ($publications as $publication) {
					DB::insertOrUpdate('publication', [
						'title'            => htmlspecialchars($publication->title),
						'author_id'        => $authorId,
						'journal_title'    => htmlspecialchars($publication?->journal?->title ?? ''),
						'published_at'     => htmlspecialchars($publication?->datePublished ?? ''),
						'times_cited'      => (int)$publication?->citationCount,
						'identifier_name'  => htmlspecialchars($publication?->identifier?->name ?? ''),
						'identifier_value' => htmlspecialchars($publication?->identifier?->value ?? ''),
						'session_id'       => $sessions[$keyNumber]['id'],
						'created_at'       => date(DATE_ATOM),
					], ['title', 'author_id']);
				}

				$parsedAuthors++;
				$parsedPublications += count($publications);
				echo "Parsed authors: $parsedAuthors, Parsed publications: $parsedPublications \n";
				sleep(1);
			}

			if ($info["http_code"] != 200 && $info["http_code"] != 201) {
				unset($sessions[$keyNumber]);
				if (count($sessions) === 0) {
					echo "Please add new keys or try later.\n";
					echo "Last parsed url: $urlForResearches";
					break;
				}
			}

			$keyNumber = ++$keyNumber % count($sessions);
			$urlForResearches = $tempResearchers?->next;
		} while (!empty($urlForResearches));

		echo 'Success';
	}
}

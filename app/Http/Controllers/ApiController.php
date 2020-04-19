<?php

namespace App\Http\Controllers;


use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Channels;
use App\Messages;
use App\User;

class ApiController extends Controller {

	const KW1 = 'coronavirus spread, coronavirus transmission, respiratory transmission, aerosol transmission, contact transmission';
	const KW2 = 'Mask, gloves, close contact, social distancing, clean hands, wash hands, cough sneeze';
	const KW3 = 'Clean surface, disinfect, clean soft surface, clean hard surface, clean electronics, laundry';
	const KW4 = 'Fever, cough, shortness of breath, short breath, sense of smell, sense of taste';
	const KW5 = 'Coronavirus test positive, separate self, self isolation, self quarantine, monitor symptoms, symptoms, mask, cover face, cover cough, cover sneeze, clean hands, wash hands, share sharing food, share sharing, clean, disinfect';
	const KW6 = 'Coronavirus test positive, separate self, self isolation, self quarantine, share sharing, clean, disinfect sharing food, share sharing, clean, disinfect';
	const KW7 = 'quarantine';
	const KW8 = 'Clean surface, disinfect, clean soft surface, clean hard surface, clean electronics, laundry, shared room, separate room, shared food, trash';
	const KW9 = 'Child safe, child coronavirus, child symptoms, child social distancing, child clean hands';
	const KW10 = 'Clean pets, pet coronavirus';
	const KW11 = 'Coronavirus high risk, coronavirus old, coronavirus HIV, coronavirus lung disease, coronavirus kidney disease, coronavirus  diabetes, long term care, nursing home, coronavirus liver disease';
	const KW12 = 'Coronavirus trends, coronavirus control, coronavirus lessons learned, flatten curve, response, handbook, manual, action guide';
	const KW13 = 'Coronavirus hospitalizations, coronavirus ICU, coronavirus ventilators, coronavirus PPE personal protection equipment, treatment facility, hospital, ICU, design, respirator, face mask, reusable supplies, optimising supply';
	const KW14 = 'Testing, tested, test kits, testing strategy, containment strategy, mitigation strategy, virus test, test, detection';
	const KW15 = 'Standard operating procedures, disinfection strategies, guidelines, regulations, advice, sterilise strategy';
	const KW16 = 'Training, support for healthcare professionals, skills training';
	const KW17 = 'Hackathon, challenge, phone tracking, self-reporting, symptom tools, online platform, platform, digital tool, digital, tele health';
	const KW18 = 'Doctor, physician, nurse, frontline, front-line';
	const KW19 = 'website, app, telegram, singer, actor, promoter';
	const KW20 = 'GIS, maps, Tableau, Dashboard, Data visualisation, data wrapper, map, chart, charted, geotag, geotagged';
	const KW21 = 'Dataset, data, infection rate, mortality rate, API, open source, public data, AWS';
	const KW22 = 'Infodemics, trends, twitter, google, sociopattern';
	const KW23 = 'Healthcare facilities, supply dataset, real-time data, real time';
	const KW24 = 'Spread, prediction, model, modelling, projection, projections, predictive, machine learning';
	const KW25 = 'Research, treatment study, research study, published, journal, drug effectiveness, drug efficacy, randomised trial, trial, cohort study';
	const KW26 = 'Coronavirus workplace, coronavirus employee, coronavirus employer, coronavirus business, prepare workplace';
	const KW27 = 'Coronavirus workplace, coronavirus employee, coronavirus employer, coronavirus business';
	const KW28 = 'Healthcare worker safe, healthcare worker PPE personal protection equipment, doctors coronavirus, nurses coronavirus, staff responsibilities, occupational safety';
	const KW29 = 'Coronavirus hospitalizations, coronavirus ICU, coronavirus ventilators, coronavirus PPE personal protection equipment';
	const KW30 = 'Coronavirus hospital, coronavirus triage';
	const KW31 = 'Coronavirus prison, coronavirus prisoner, correctional facility, inmate';
	const KW32 = 'Funeral, burials';
	const KW33 = 'Disinfect food, homemade';
	const KW34 = 'Idea, collaboration, partnership, volunteer, mobilisation';

	const KEYWORDS = [
		1  => self::KW1,
		2  => self::KW2,
		3  => self::KW3,
		4  => self::KW4,
		5  => self::KW5,
		6  => self::KW6,
		7  => self::KW7,
		8  => self::KW8,
		9  => self::KW9,
		10 => self::KW10,
		11 => self::KW11,
		12 => self::KW12,
		13 => self::KW13,
		14 => self::KW14,
		15 => self::KW15,
		16 => self::KW16,
		17 => self::KW17,
		18 => self::KW18,
		19 => self::KW19,
		20 => self::KW20,
		21 => self::KW21,
		22 => self::KW22,
		23 => self::KW23,
		24 => self::KW24,
		25 => self::KW25,
		26 => self::KW26,
		27 => self::KW27,
		28 => self::KW28,
		29 => self::KW29,
		30 => self::KW30,
		31 => self::KW31,
		32 => self::KW32,
		33 => self::KW33,
		34 => self::KW34,
	];

	/**
	 * Display a listing of the resource.
	 *
	 */
	public function users() {
		$data  = [];
		$users = User::all();;
		foreach ( $users as $member ) {
			$data[] = [
				$member->name,
				$member->email,
				//$member->avatar
			];
		}

		return response()->stream( function () use ( $data ) {
			$out = fopen( 'php://output', 'w' );
			foreach ( $data as $record ) {
				fputcsv( $out, $record );
			}
			fclose( $out );
		} );


	}

	/**
	 * Display a listing of the resource.
	 *
	 */
	public function channels() {
		$data     = [];
		$channels = Channels::all();
		foreach ( $channels as $channel ) {
			$data[] = [
				$channel->name,
				$channel->topic,
				$channel->num_members,
			];
		}

		return response()->stream( function () use ( $data ) {
			$out = fopen( 'php://output', 'w' );
			foreach ( $data as $record ) {
				fputcsv( $out, $record );
			}
			fclose( $out );
		} );


	}

	public function messages( Request $request ) {
		$keyword_id = $request->get( 'keyword_id' ) ?? false;
		$data       = [];

		$messages = Messages::all();
		foreach ( $messages as $message ) {
			$urls = $this->extract_urls( $message->message );
			if ( ! empty( $urls ) ) {
				$urls = array_map( function ( $item ) {
					$split = explode( '|', $item );

					return current( array_unique( $split ) );
				}, $urls );

				if ( empty( $keyword_id ) ) {
					$data[] = [
						date( "d/m/Y H:i:s", strtotime($message->ts) ),
						strip_tags( $message->message ),
						implode( " \n ", $urls ),
						$message->file_url ?? ''
					];
				} else {
					$keywords = self::KEYWORDS[ intval( $keyword_id ) ];
					if ( $this->matches_keyword( $keywords, $message->text ) ) {
						$data[] = [
							date( "d/m/Y H:i:s", $message->ts ),
							strip_tags( $message->text ),
							implode( " \n ", $urls ),
							$message->files[0]->url_private ?? ''
						];
					}
				}
			}


		}


		return response()->stream( function () use ( $data ) {
			$out = fopen( 'php://output', 'w' );
			foreach ( $data as $record ) {
				fputcsv( $out, $record );
			}
			fclose( $out );
		} );

	}


	private function extract_urls( $text ) {
		preg_match_all( '#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $text, $match );

		return $match[0] ?? [];

	}

	private function matches_keyword( $key_word_string, $text ) {
		$array_keywords = explode( ' ',
			preg_replace( '#\.([a-zA-Z])#', '. $1', preg_replace( '/\s+/', ' ', $key_word_string ) ) );
		$array_keywords = array_map( function ( $item ) {
			return rtrim( $item, ',' );
		}, $array_keywords );

		foreach ( $array_keywords as $keys ) {
			if ( strpos( $text, $keys ) ) {
				return true;
			}
		}

		return false;
	}
}

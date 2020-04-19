<?php

namespace App\Http\Controllers;

use SlackApi;
use SlackChannel;
use SlackChat;
use SlackFile;
use SlackGroup;
use SlackUser;
use Illuminate\Http\Response;

class ApiController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 */
	public function users() {
		$data  = [];
		$users = SlackUser::lists();;
		foreach ( $users->members as $member ) {
			$real_name = $member->profile->real_name ?? '';
			$data[]    = [
				! empty( $real_name ) ? sprintf( "%s (%s)", $real_name, $member->name ) : $member->name,
				$member->profile->email ?? '',
				//$member->profile->image_72
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
		$channels = SlackChannel::lists( 0 );
		foreach ( $channels->channels as $channel ) {
			$data[] = [
				$channel->name,
				$channel->purpose->value,
				$channel->num_members,
			];
		}

		/*
				$groups = SlackGroup::lists( 0 );
				foreach ( $groups->groups as $group ) {
					var_dump($group);exit;
					$data[] = [
						$group->name,
						$group->purpose->value,
						$group->num_members,
					];
				}*/

		return response()->stream( function () use ( $data ) {
			$out = fopen( 'php://output', 'w' );
			foreach ( $data as $record ) {
				fputcsv( $out, $record );
			}
			fclose( $out );
		} );


	}

	public function messages() {
		$data = [];

		$limit        = 300;
		$channel_list = SlackChannel::lists( 0 );
		foreach ( $channel_list->channels as $c ) {
			if ( empty( $c->is_group ) ) {
				$channel_messages = SlackChannel::history( $c->id, $limit );
			} else {
				$channel_messages = SlackGroup::history( $c->id, $limit );
			}

			if ( ! isset( $channel_messages->messages ) ) {
				continue;
			}

			foreach ( $channel_messages->messages as $message ) {

				if ( ! isset( $message ) ) {
					continue;
				}

				if ( isset( $message->bot_id ) ) {
					continue;
				}

				if ( ! isset( $message->user ) ) {
					continue;
				}

				if ( ! isset( $message->text ) ) {
					continue;
				}


				if ( empty( $message->user ) ) {
					continue;
				}

				$urls = $this->extract_urls( $message->text );
				if ( ! empty( $urls ) ) {
					$urls = array_map( function ( $item ) {
						$split = explode( '|', $item );

						return current( array_unique( $split ) );
					}, $urls );

					$data[] = [
						date( "d/m/Y H:i:s", $message->ts ),
						strip_tags( $message->text ),
						implode( " \n ", $urls ),
						$message->files[0]->url_private ?? ''
					];
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

}

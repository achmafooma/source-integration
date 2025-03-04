<?php

# Copyright (c) 2012 John Reese
# Licensed under the MIT license

if ( false === include_once( config_get( 'plugin_path' ) . 'Source/MantisSourceGitBasePlugin.class.php' ) ) {
	return;
}

require_once( config_get( 'core_path' ) . 'url_api.php' );

class SourceGitwebPlugin extends MantisSourceGitBasePlugin {

	const PLUGIN_VERSION = '2.1.1';
	const FRAMEWORK_VERSION_REQUIRED = '2.5.0';

	public $type = 'gitweb';

	public function url_get_auth( $p_url, $p_user, $p_pass ) {
		if( strlen( $p_user ) > 0 && strlen( $p_pass ) > 0 ) {
			$t_url_parts = preg_split("/:\\/\\//", $p_url );
			$p_url = $t_url_parts[0] . "://" . $p_user . ":" . $p_pass . "@" .$t_url_parts[1];
		}
		return url_get( $p_url );
	}

	public function show_type() {
		return plugin_lang_get( 'gitweb' );
	}

	public function show_changeset( $p_repo, $p_changeset ) {
		$t_ref = substr( $p_changeset->revision, 0, 8 );
		$t_branch = $p_changeset->branch;

		return "$t_branch $t_ref";
	}

	public function show_file( $p_repo, $p_changeset, $p_file ) {
		return  "$p_file->action - $p_file->filename";
	}

	private function uri_base( $p_repo ) {
		$t_uri_base = $p_repo->info['gitweb_root'] . '?p=' . $p_repo->info['gitweb_project'] . ';';

		return $t_uri_base;
	}

	public function url_repo($p_repo, $p_changeset=null ) {
		return $this->uri_base( $p_repo ) . ( $p_changeset ? 'h=' . $p_changeset->revision : '' );
	}

	public function url_changeset( $p_repo, $p_changeset ) {
		return $this->uri_base( $p_repo ) . 'a=commitdiff;h=' . $p_changeset->revision;
	}

	public function url_file( $p_repo, $p_changeset, $p_file ) {
		return $this->uri_base( $p_repo ) . 'a=blob;f=' . $p_file->filename .
			';h=' . $p_file->revision . ';hb=' . $p_changeset->revision;
	}

	public function url_diff( $p_repo, $p_changeset, $p_file ) {
		return $this->uri_base( $p_repo ) . 'a=blobdiff;f=' . $p_file->filename .
			';h=' . $p_file->revision . ';hb=' . $p_changeset->revision . ';hpb=' . $p_changeset->parent;
	}

	public function update_repo_form( $p_repo ) {
		$t_gitweb_root = null;
		$t_gitweb_project = null;
		$t_gitweb_user = null;
		$t_gitweb_pass = null;

		if ( isset( $p_repo->info['gitweb_root'] ) ) {
			$t_gitweb_root = $p_repo->info['gitweb_root'];
		}

		if ( isset( $p_repo->info['gitweb_project'] ) ) {
			$t_gitweb_project = $p_repo->info['gitweb_project'];
		}

		if ( isset( $p_repo->info['gitweb_user'] ) ) {
			$t_gitweb_user = $p_repo->info['gitweb_user'];
		}

		if ( isset( $p_repo->info['gitweb_pass'] ) ) {
			$t_gitweb_pass = $p_repo->info['gitweb_pass'];
		}


		if ( isset( $p_repo->info['master_branch'] ) ) {
			$t_master_branch = $p_repo->info['master_branch'];
		} else {
			$t_master_branch = $this->get_default_primary_branches();
		}
?>
<tr>
	<th class="category">
		<label for="gitweb_root">
			<?php echo plugin_lang_get( 'gitweb_root' ) ?>
		</label>
	</th>
	<td>
		<input id="gitweb_root" name="gitweb_root"
			   type="text" maxlength="250" size="40"
			   value="<?php echo string_attribute( $t_gitweb_root ) ?>"/>
	</td>
</tr>
<tr>
	<th class="category">
		<label for="gitweb_project">
			<?php echo plugin_lang_get( 'gitweb_project' ) ?>
		</label>
	</th>
	<td>
		<input id="gitweb_project" name="gitweb_project"
			   type="text" maxlength="250" size="40"
			   value="<?php echo string_attribute( $t_gitweb_project ) ?>"/>
	</td>
</tr>
<tr>
	<th class="category">
		<label for="gitweb_user">
			<?php echo plugin_lang_get( 'gitweb_user' ) ?>
		</label>
	</th>
	<td>
		<input id="gitweb_user" name="gitweb_user"
			   type="text" maxlength="250" size="40"
			   value="<?php echo string_attribute( $t_gitweb_user ) ?>"/>
	</td>
</tr>
<tr>
	<th class="category">
		<label for="gitweb_pass">
			<?php echo plugin_lang_get( 'gitweb_pass' ) ?>
		</label>
	</th>
	<td>
		<input id="gitweb_pass" name="gitweb_pass"
			   type="password" maxlength="250" size="40"
			   value="<?php echo string_attribute( $t_gitweb_pass ) ?>"/>
	</td>
</tr>
<tr>
	<th class="category">
		<label for="master_branch">
			<?php echo plugin_lang_get( 'master_branch' ) ?>
		</label>
	</th>
	<td>
		<input id="master_branch" name="master_branch"
			   type="text" maxlength="250" size="40"
			   value="<?php echo string_attribute( $t_master_branch ) ?>"/>
	</td>
</tr>
<?php
	}

	public function update_repo( $p_repo ) {
		$f_gitweb_root = gpc_get_string( 'gitweb_root' );
		$f_gitweb_project = gpc_get_string( 'gitweb_project' );
		$f_gitweb_user = gpc_get_string( 'gitweb_user' );
		$f_gitweb_pass = gpc_get_string( 'gitweb_pass' );
		$f_master_branch = gpc_get_string( 'master_branch' );

		$this->validate_branch_list( $f_master_branch );

		$p_repo->info['gitweb_root'] = $f_gitweb_root;
		$p_repo->info['gitweb_project'] = $f_gitweb_project;
		$p_repo->info['gitweb_user'] = $f_gitweb_user;
		$p_repo->info['gitweb_pass'] = $f_gitweb_pass;
		$p_repo->info['master_branch'] = $f_master_branch;

		return $p_repo;
	}

	public function precommit( ) {
		# TODO: Implement real commit sequence.
		return;
	}

	public function commit( $p_repo, $p_data ) {
		# Handle branch names with '+' character
		$p_data = str_replace('_plus_', '+', $p_data);

		# The -d option from curl requires you to encode your own data.
		# Once it reaches here it is decoded. Hence we split by a space
		# were as the curl command uses a '+' character instead.
		# i.e. DATA=`echo $INPUT | sed -e 's/ /+/g'`
		list ( , $t_commit_id, $t_branch) = explode(' ', $p_data);
		list ( , , $t_branch) = explode('/', $t_branch, 3);

		# master_branch contains comma-separated list of branches
		$t_branches = explode(',', $p_repo->info['master_branch']);
		if (!in_array('*', $t_branches) and !in_array($t_branch, $t_branches))
		{
			return array();
		}

		return $this->import_commits($p_repo, null, $t_commit_id, $t_branch);
	}

	public function import_full( $p_repo ) {
		echo '<pre>';

		$t_branch = $p_repo->info['master_branch'];
		if ( is_blank( $t_branch ) ) {
			$t_branch = $this->get_default_primary_branches();
		}

		if ($t_branch != '*')
		{
			$t_branches = array_map( 'trim', explode( ',', $t_branch ) );
		}
		else
		{
			$t_heads_url = $this->uri_base( $p_repo ) . 'a=heads';
			$t_branches_input = $this->url_get_auth( $t_heads_url, $p_repo->info['gitweb_user'], $p_repo->info['gitweb_pass'] );

			$t_branches_input = str_replace( array("\r", "\n", '&lt;', '&gt;', '&nbsp;'), array('', '', '<', '>', ' '), $t_branches_input );

			$t_branches_input_p1 = strpos( $t_branches_input, '<table class="heads">' );
			$t_branches_input_p2 = strpos( $t_branches_input, '<div class="page_footer">' );
			$t_gitweb_heads = substr( $t_branches_input, $t_branches_input_p1, $t_branches_input_p2 - $t_branches_input_p1 );
			preg_match_all( '/<a class="list name".*>(.*)<\/a>/iU', $t_gitweb_heads, $t_matches, PREG_SET_ORDER );

			$t_branches = array();
			foreach ($t_matches as $match)
			{
				$t_branch = trim($match[1]);
				if ($match[1] != 'origin' and !in_array($t_branch,$t_branches))
				{
					$t_branches[] = $t_branch;
				}
			}
		}

		$t_changesets = array();

		$t_changeset_table = plugin_table( 'changeset', 'Source' );

		foreach( $t_branches as $t_branch ) {
			$t_query = "SELECT parent FROM $t_changeset_table
				WHERE repo_id=" . db_param() . ' AND branch=' . db_param() .
				' ORDER BY timestamp';
			$t_result = db_query( $t_query, array( $p_repo->id, $t_branch ), 1 );

			$t_commits = array( $t_branch );

			if ( db_num_rows( $t_result ) > 0 ) {
				$t_parent = db_result( $t_result );
				echo "Oldest '$t_branch' branch parent: '$t_parent'\n";

				if ( !empty( $t_parent ) ) {
					$t_commits[] = $t_parent;
				}
			}

			$t_changesets = array_merge( $t_changesets, $this->import_commits( $p_repo, $this->uri_base( $p_repo ), $t_commits, $t_branch  ) );
		}

		echo '</pre>';

		return $t_changesets;
	}

	public function import_latest( $p_repo ) {
		return $this->import_full( $p_repo );
	}

	private function import_commits( $p_repo, $p_uri_base, $p_commit_ids, $p_branch='' ) {
		static $s_parents = array();
		static $s_counter = 0;

		if ( is_array( $p_commit_ids ) ) {
			$s_parents = array_merge( $s_parents, $p_commit_ids );
		} else {
			$s_parents[] = $p_commit_ids;
		}

		$t_changesets = array();

		while( count( $s_parents ) > 0 && $s_counter < 200 ) {
			$t_commit_id = array_shift( $s_parents );

			echo "Retrieving $t_commit_id ... ";

			# Handle branch names with '+' character
			$t_fixed_id = str_replace('+', '%2B', $t_commit_id);
			$t_commit_url = $this->uri_base( $p_repo ) . 'a=commit;h=' . $t_fixed_id;
			$t_input = $this->url_get_auth( $t_commit_url, $p_repo->info['gitweb_user'], $p_repo->info['gitweb_pass'] );

			if ( !$t_input ) {
				echo "failed.\n";
				echo "'$t_commit_url' did not return any data.\n";
				die();
			}

			list( $t_changeset, $t_commit_parents ) = $this->commit_changeset( $p_repo, $t_input, $p_branch );
			if ( !is_null( $t_changeset ) ) {
				$t_changesets[] = $t_changeset;
			}

			$s_parents = array_merge( $s_parents, $t_commit_parents );
			$s_counter += 1;
		}

		$s_counter = 0;
		return $t_changesets;
	}

	private function commit_changeset( $p_repo, $p_input, $p_branch='' ) {

		$t_input = str_replace( array("\r", "\n", '&lt;', '&gt;', '&nbsp;'), array('', '', '<', '>', ' '), $p_input );

		# Extract sections of commit data and changed files
		$t_input_p1 = strpos( $t_input, '<div class="title_text">' );
		$t_input_p2 = strpos( $t_input, '<div class="list_head">' );
		if ( false === $t_input_p1 || false === $t_input_p2 ) {
			echo "commit data failure.\n";
			var_dump( strlen( $t_input ), $t_input_p1, $t_input_p2 );
			die();
		}
		$t_gitweb_data = substr( $t_input, $t_input_p1, $t_input_p2 - $t_input_p1 );

		$t_input_p1 = strpos( $t_input, '<table class="diff_tree">' );

		if ( false === $t_input_p1) {
			$t_input_p1 = strpos( $t_input, '<table class="combined diff_tree">' );
		}

		$t_input_p2 = strpos( $t_input, '<div class="page_footer">' );
		if ( false === $t_input_p1 || false === $t_input_p2 ) {
			echo 'file data failure.';
			var_dump( strlen( $t_input ), $t_input_p1, $t_input_p2 );
			die();
		}
		$t_gitweb_files = substr( $t_input, $t_input_p1, $t_input_p2 - $t_input_p1 );

		# Get commit revsion and make sure it's not a dupe
		preg_match( '#<tr><td>commit</td><td class="sha1">([a-f0-9]*)</td></tr>#', $t_gitweb_data, $t_matches );
		$t_commit['revision'] = $t_matches[1];

		echo "processing $t_commit[revision] ... ";
		if ( !SourceChangeset::exists( $p_repo->id, $t_commit['revision'] ) ) {

			# Parse for commit data
			preg_match( '#authored by ([^"]*).*?authored by ([^"]*).*?>([^<]*\d*:\d*:\d*[^(<]*)'
					. '.*?committed by ([^"]*).*?committed by ([^"]*).*?page_body">(.*?)</div>#',
				$t_gitweb_data, $t_matches );
			$t_commit['author'] = $t_matches[1];
			$t_commit['author_email'] = $t_matches[2];
			$t_commit['date'] = date( 'Y-m-d H:i:s', strtotime( $t_matches[3] ) );
			$t_commit['committer'] = $t_matches[4];
			$t_commit['committer_email'] = $t_matches[5];
			$t_commit['message'] = trim( str_replace( '<br/>', PHP_EOL, $t_matches[6] ) );

			$t_parents = array();
			if ( preg_match_all( '#parent</td><td class="sha1"><[^>]*(?:h=|/commit/)([0-9a-f]*)#', $t_gitweb_data, $t_matches ) ) {
				foreach( $t_matches[1] as $t_match ) {
					$t_parents[] = $t_commit['parent'] = $t_match;
				}
			}

			# Strip ref links and signoff spans from commit message
			$t_commit['message'] = preg_replace( array( '#<a[^>]*>([^<]*)</a>#', '#<span[^>]*>(.*?)</span>#' ),
				'$1', $t_commit['message'] );

			# Prepend a # sign to mantis number
			$t_commit['message'] = preg_replace( '#(mantis)\s+(\d+)#i', '$1 #$2',$t_commit['message'] );

			# Parse for changed file data
			$t_commit['files'] = array();

			preg_match_all( '#class="list".*?h=(\w*).*?>(.*?)<.a>(?:(?:<.td><td><span class="file_status| with \d*%) (\w*))?#',
				$t_gitweb_files, $t_matches, PREG_SET_ORDER );

			foreach( $t_matches as $t_file_matches ) {
				$t_file = array();
				$t_file['filename'] = $t_file_matches[2];
				$t_file['revision'] = $t_file_matches[1];

				if ( isset( $t_file_matches[3] ) ) {
					if ( $t_file_matches[3] == 'new' || $t_file_matches[3] == 'moved' ) {
						$t_file['action'] = SourceFile::ADDED;
					} else if ( $t_file_matches[3] == 'deleted' || $t_file_matches[3] == 'similarity' ) {
						$t_file['action'] = SourceFile::DELETED;
					} else {
						$t_file['action'] = SourceFile::MODIFIED;
					}
				} else {
					$t_file['action'] = SourceFile::MODIFIED;
				}

				$t_commit['files'][] = $t_file;
			}

			$t_changeset = new SourceChangeset( $p_repo->id, $t_commit['revision'], $p_branch,
				$t_commit['date'], $t_commit['author'], $t_commit['message'], 0,
				( isset( $t_commit['parent'] ) ? $t_commit['parent'] : '' ) );

			$t_changeset->author_email = $t_commit['author_email'];
			$t_changeset->committer = $t_commit['committer'];
			$t_changeset->committer_email = $t_commit['committer_email'];

			foreach( $t_commit['files'] as $t_file ) {
				$t_changeset->files[] = new SourceFile( 0, $t_file['revision'], $t_file['filename'], $t_file['action'] );
			}

			$t_changeset->save();

			echo "saved.\n";
			return array( $t_changeset, $t_parents );
		} else {
			echo "already exists.\n";
			return array( null, array() );
		}
	}
}

<?php

class ys_badge_event
{

	function process_event($event, $userid, $handle, $cookieid, $params)
	{
		if (qa_opt('ys_badge_active')) {
			switch ($event) {
				// when a new question, answer or comment is created. The $params array contains full information about the new post, including its ID in $params['postid'] and textual content in $params['text'].
				case 'q_post':
					$this->question_post($event, $userid, $params);
					break;
				case 'a_post':
					$this->answer_post($event, $userid, $params);
					break;
				case 'c_post':
					$this->comment_post($event, $userid, $params);
					break;

				// when a question, answer or comment is modified. The $params array contains information about the post both before and after the change, e.g. $params['content'] and $params['oldcontent'].
				case 'q_edit':
					// $this->question_edit($event, $userid, $params);
					break;
				case 'a_edit':
					// $this->answer_edit($event, $userid, $params);
					break;
				case 'c_edit':
					// $this->comment_edit($event, $userid, $params);
					break;

				// when an answer is selected or unselected as the best answer for its question. The IDs of the answer and its parent question are in $params['postid'] and $params['parentid'] respectively.
				case 'a_select':
					// $this->answer_select($event, $userid, $params);
					break;
				case'a_unselect':
					break;

				// when a question, answer or comment is hidden or shown again after being hidden. The ID of the question, answer or comment is in $params['postid'].
				case 'q_hide':
				case 'a_hide':
				case 'c_hide':
				case 'q_reshow':
				case 'a_reshow':
				case 'c_reshow':
					break;

				// when a question, answer or comment is permanently deleted (after being hidden). The ID of the appropriate post is in $params['postid'].
				case 'a_delete':
				case 'q_delete':
				case 'c_delete':
					break;

				// when an anonymous question, answer or comment is claimed by a user with a matching cookie clicking 'I wrote this'. The ID of the post is in $params['postid'].
				case 'q_claim':
				case 'a_claim':
				case 'c_claim':
					break;

				// when a question is moved to a different category, with more details in $params.
				case 'q_move':
					break;

				// when an answer is converted into a comment, with more details in $params.
				case 'a_to_c':
					break;

				// when a question or answer is upvoted, downvoted or unvoted by a user. The ID of the post is in $params['postid'].
				case 'q_vote_up':
					// $this->question_vote_up($event, $userid, $params);
					break;
				case 'a_vote_up':
					// $this->answer_vote_up($event, $userid, $params);
					break;
				case 'q_vote_down':
					// $this->question_vote_down($event, $userid, $params);
					break;
				case 'a_vote_down':
					// $this->answer_vote_down($event, $userid, $params);
					break;
				case 'c_vote_up':
					// $this->comment_vote_up($event, $userid, $params);
					break;
				case 'c_vote_down':
					// $this->check_voter($userid);
					break;
				case 'q_vote_nil':
				case 'a_vote_nil':
					break;
				// when a question, answer or comment is flagged or unflagged. The ID of the question, answer or comment is in $params['postid'].
				case 'q_flag':
					// $this->question_flag($event, $userid, $params);
					break;
				case 'a_flag':
					// $this->answer_flag($event, $userid, $params);
					break;
				case 'c_flag':
					// $this->comment_flag($event, $userid, $params);
					break;
				case 'q_unflag':
					break;
				case 'a_unflag':
					break;
				case 'c_unflag':
					break;

				// when a new user registers. The email is in $params['email'] and the privilege level in $params['level'].
				case 'u_register':
					break;

				// when a user logs in or out of Q2A.
				case 'u_login':
				case 'u_logout':
					break;

				// when a user successfully confirms their email address, given in $params['email'].
				case 'u_confirmed':
					// $this->check_email_award($event, $userid, $params);
					break;

				// when a user successfully resets their password, which was emailed to $params['email'].
				case 'u_reset':
					break;

				// when a user saves (and has possibly changed) their Q2A account details.
					// check for full details
				case 'u_save':
					// $this->check_user_fields($userid, $params);
					break;

				// when a user sets (and has possibly changed) their Q2A password.
				case 'u_password':
					break;

				// when a user's account details are saved by someone other than the user, i.e. an admin. Note that the $userid and $handle parameters to the process_event() function identify the user making the changes, not the user who is being changed. Details of the user being changed are in $params['userid'] and $params['handle'].
				case 'u_edit':
					break;

				// when a user's privilege level is changed by a different user. See u_edit above for how the two users are identified. The old and new levels are in $params['level'] and $params['oldlevel'].
					//$this->priviledge_flag($params['level'],$params['userid']);
				case 'u_level':
					break;

				// when a user is blocked or unblocked by another user. See u_edit above for how the two users are identified.
				case 'u_block':
				case 'u_unblock':
					break;

				// when a message is sent via the Q2A feedback form, with more details in $params.
				case 'feedback':
					break;

				// when a search is performed. The search query is in $params['query'] and the start position in $params['start'].
				case 'search':
					break;
			}
		}
	}

	private function question_post($event, $event_user, $params)
	{

	}

	private function answer_post($event, $event_user, $params)
	{

	}


	private function comment_post($event, $event_user, $params)
	{

	}

}

/*
	Omit PHP closing tag to help avoid accidental output
*/

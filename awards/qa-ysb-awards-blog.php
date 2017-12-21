<?php

/**
 * 飼育日誌系のバッジ付与のベースクラス
 */
abstract class qa_ysb_awards_blog_base extends qa_ysb_awards_base
{

    public function get_award_target($event, $post_userid, $params)
    {
        if($event == 'qas_blog_b_post' && $this->check_award_badge($post_userid, $params)){
            return array($post_userid);
        }
        return array();
    }

    public function check_award_badge($userid, $params)
    {
        return true;
    }
}

/*
 * 記録者
 * 飼育日誌を投稿する
 */
class qa_ysb_awards_recorder extends qa_ysb_awards_blog_base
{
    public function get_badgeid()
    {
        return 301;
    }

    public function get_target_users_from_achievement($exclude)
    {
        $sql = 'SELECT DISTINCT userid';
        $sql.= ' FROM ^blogs';
        $sql.= ' WHERE type="B"';
        $sql.= ' AND userid IS NOT NULL';
        if (!empty($exclude)) {
            $sql.= qa_db_apply_sub(' AND userid NOT IN (#)', array($exclude));
        }
        $sql.= ' ORDER BY userid';
        return qa_db_read_all_values(qa_db_query_sub($sql));
    }

}

/*
 * 人気の飼育日誌
 * 飼育日誌にいいね！が3つ以上つく
 */
class qa_ysb_awards_good_blog extends qa_ysb_awards_blog_base
{
    const UPVOTE_THRESHOLD = 3;

    public function get_badgeid()
    {
        return 302;
    }

    public function get_award_target($event, $post_userid, $params)
    {
        if ($event == 'qas_blog_vote_up' && $this->check_award_badge($post_userid, $params)) {
            return array($params['userid']);
        }
        return array();
    }

    public function check_award_badge($userid, $params)
    {
        $sql = 'SELECT count(*)';
        $sql .= ' FROM ^blogs WHERE upvotes >= #';
        $sql .= ' AND type="B" AND postid = #';
        $count = qa_db_read_one_value(qa_db_query_sub($sql, self::UPVOTE_THRESHOLD, $params['postid']));

        if ($count > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function get_target_users_from_achievement($exclude)
    {
        $sql = 'SELECT DISTINCT userid';
        $sql.= ' FROM ^blogs';
        $sql.= ' WHERE upvotes >= #';
        $sql.= ' AND type="B"';
        $sql.= ' AND userid IS NOT NULL';
        if (!empty($exclude)) {
            $sql.= qa_db_apply_sub(' AND userid NOT IN (#)', array($exclude));
        }
        $sql.= ' ORDER BY userid';
        return qa_db_read_all_values(qa_db_query_sub($sql, self::UPVOTE_THRESHOLD));
    }
}

/*
 * 反響多数
 * コメントが5件以上投稿される
 */
class qa_ysb_awards_blog_with_many_comment extends qa_ysb_awards_blog_base
{
    const COMMENT_THRESHOLD = 5;

    public function get_badgeid()
    {
        return 303;
    }

    public function get_award_target($event, $post_userid, $params)
    {
        if ($event == 'qas_blog_c_post' && $this->check_award_badge($post_userid, $params)) {
            return array($params['parent']['userid']);
        }
        return array();
    }

    public function check_award_badge($userid, $params)
    {
        $sql = 'SELECT count(c.postid)';
        $sql .= ' FROM ^blogs b';
        $sql .= " LEFT JOIN ^blogs c ON c.parentid = b.postid and c.type = 'C'";
        $sql .= "WHERE b.type = 'B' and b.postid = #";
        $count = qa_db_read_one_value(qa_db_query_sub($sql, $params['parent']['postid']));

        if ($count >= self::COMMENT_THRESHOLD) {
            return true;
        } else {
            return false;
        }
    }

    public function get_target_users_from_achievement($exclude)
    {
        $sql = 'SELECT DISTINCT b.userid';
        $sql.= ' FROM ^blogs b';
        $sql.= ' LEFT JOIN (';
        $sql.= '   SELECT count(*) AS cnt, parentid';
        $sql.= ' FROM qa_blogs';
        $sql.= " WHERE type='C'";
        $sql.= " GROUP BY parentid";
        $sql.= ") c";
        $sql.= " ON b.postid = c.parentid";
        $sql.= " WHERE cnt >= #";
        $sql.= " AND type = 'B'";
        $sql.= ' AND b.userid IS NOT NULL';
        if (!empty($exclude)) {
            $sql.= qa_db_apply_sub(' AND b.userid NOT IN (#)', array($exclude));
        }
        $sql.= ' ORDER BY b.userid';
        return qa_db_read_all_values(qa_db_query_sub($sql, self::COMMENT_THRESHOLD));
    }
}

/*
 * 詳しい飼育日誌
 * 500文字以上の飼育日誌を投稿する
 */
class qa_ysb_awards_detail_blog extends qa_ysb_awards_blog_base
{
    const CHAR_LENGTH_THRESHOLD = 500;

    public function get_badgeid()
    {
        return 304;
    }

    public function check_award_badge($userid, $params)
    {
        $content = strip_tags($params['content']);
        if (mb_strlen($content, "UTF-8") >= self::CHAR_LENGTH_THRESHOLD) {
            return true;
        } else {
            return false;
        }
    }

    public function get_target_users_from_achievement($exclude)
    {
        $sql = 'SELECT a.userid, a.content';
        $sql.= ' FROM ^blogs a';
        $sql.= ' INNER JOIN';
        $sql.= ' (SELECT userid, MAX(CHAR_LENGTH(content)) AS clen';
        $sql.= '   FROM ^blogs';
        $sql.= '   WHERE userid IS NOT NULL';
        if (!empty($exclude)) {
            $sql.= qa_db_apply_sub(' AND userid NOT IN (#)', array($exclude));
        }
        $sql.= "   AND type = 'B'";
        $sql.= '   AND CHAR_LENGTH(content) >= #';
        $sql.= '   GROUP BY userid) b';
        $sql.= ' ON a.userid = b.userid';
        $sql.= ' AND CHAR_LENGTH(a.content) = b.clen';
        $posts = qa_db_read_all_assoc(qa_db_query_sub($sql, self::CHAR_LENGTH_THRESHOLD));
        $users = array();
        foreach ($posts as $post) {
            if (mb_strlen(strip_tags($post['content']), "UTF-8") > self::CHAR_LENGTH_THRESHOLD) {
                $users[] = $post['userid'];
            }
        }
        return $users;
    }
}

/*
 * 画像付き飼育日誌
 * 画像付きの飼育日誌を投稿する
 */
class qa_ysb_awards_blog_with_image extends qa_ysb_awards_blog_base
{
    public function get_badgeid()
    {
        return 305;
    }

    public function check_award_badge($userid, $params)
    {
        $content = $params['content'];
        $regex = "/\[image=\"?[^\"\]]+\"?\]/isU";
        if(preg_match($regex, $content, $matches)) {
            return true;
        } else {
            return false;
        }
    }
}

/*
 * 動画付き飼育日誌
 * 動画付きの飼育日誌を投稿する
 */
class qa_ysb_awards_blog_with_video extends qa_ysb_awards_blog_base
{
    public function get_badgeid()
    {
        return 306;
    }

    public function check_award_badge($userid, $params)
    {
        $content = $params['content'];
        $regex = "/\[uploaded-video=\"?[^\"\]]+\"?\]/isU";
        if(preg_match($regex, $content, $matches)) {
            return true;
        } else {
            return false;
        }
    }
}

/*
 * 継続力
 * 3件の飼育日誌を投稿する
 */
class qa_ysb_awards_blog_continuing extends qa_ysb_awards_blog_base
{
    const BLOG_COUNT = 3;

    public function get_badgeid()
    {
        return 307;
    }

    public function check_award_badge($userid, $params)
    {
        $sql = "SELECT count(*)";
        $sql.= " FROM ^blogs";
        $sql.= " WHERE type='B'";
        $sql.= " AND userid=#";

        $count = qa_db_read_one_value(qa_db_query_sub($sql, $userid));
        if ($count >= self::BLOG_COUNT) {
            return true;
        } else {
            return false;
        }
    }
}

/*
 * マメな記録者
 * 10件の飼育日誌を投稿する
 */
class qa_ysb_awards_diligent_recorder extends qa_ysb_awards_blog_base
{
    const BLOG_COUNT = 10;

    public function get_badgeid()
    {
        return 308;
    }

    public function check_award_badge($userid, $params)
    {
        $sql = "SELECT count(*)";
        $sql.= " FROM ^blogs";
        $sql.= " WHERE type='B'";
        $sql.= " AND userid=#";

        $count = qa_db_read_one_value(qa_db_query_sub($sql, $userid));
        if ($count >= self::BLOG_COUNT) {
            return true;
        } else {
            return false;
        }
    }
}

/*
 * 記録の達人
 * 30件の飼育日誌を投稿する
 */
class qa_ysb_awards_recording_expert extends qa_ysb_awards_blog_base
{
    const BLOG_COUNT = 30;

    public function get_badgeid()
    {
        return 309;
    }

    public function check_award_badge($userid, $params)
    {
        $sql = "SELECT count(*)";
        $sql.= " FROM ^blogs";
        $sql.= " WHERE type='B'";
        $sql.= " AND userid=#";

        $count = qa_db_read_one_value(qa_db_query_sub($sql, $userid));
        if ($count >= self::BLOG_COUNT) {
            return true;
        } else {
            return false;
        }
    }
}
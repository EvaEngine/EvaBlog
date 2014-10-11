<?php
/**
 * Wscn
 *
 * @link      https://github.com/wallstreetcn/wallstreetcn
 * @copyright Copyright (c) 2010-2014 WallstreetCN
 * @author    WallstreetCN Team: shao<liujaysen@gmail.com>
 */
namespace Eva\EvaBlog\Models;

use Eva\EvaBlog\Entities\Votes;
use Eva\EvaBlog\Entities\Posts;

use Eva\EvaBlog\Entities\VotesUsers;
use Eva\EvaEngine\Mvc\Model as BaseModel;

class Vote extends BaseModel
{

//    public function findVotesByUserId($userId,$commentIds = null)
//    {
//        $builder = $this->getModelsManager()->createBuilder();
//
//        $builder->from('Eva\EvaComment\Entities\Votes');
//
//        $builder->andWhere('userId = :userId:',array('userId'=>$userId));
//        if(is_array($commentIds)){
//            $builder->inWhere('commentId', $commentIds);
//        }
//
//        $votes = $builder->getQuery()->execute();
//        return $votes;
//    }

    public function createVote(Posts $post,$userId,$action)
    {
        $userVote = new VotesUsers();

        $userVote->postId = $post->id;
        $userVote->userId = $userId;
        $userVote->voteType = $action;

        return $userVote;
    }

    public function findVote(Posts $post,$userId,$action)
    {
        return VotesUsers::findFirst("postId='$post->id' AND userId='$userId' AND voteType='$action'");
    }

    public function saveVote(Posts $post,VotesUsers $userVote)
    {
        $action = $userVote->voteType;
        $userVote->save();
        $vote = Votes::findFirst(array("postId = $post->id",'for_update'=>true));
        if (!$vote) {
            $vote = new Votes();
            $action == Votes::TYPE_UP ? $vote->upVote = 1 : $vote->downVote = 1;
        } else
            $vote->upVote++;

        $vote->postId = $post->id;
        $vote->lastVotedAt = time();
        $vote->save();
    }

    public function removeVote(Posts $post,VotesUsers $userVote){
        $action = $userVote->voteType;
        $userVote->delete();

        $vote = Votes::findFirst(array("postId = $post->id",'for_update'=>true));
        if (!$vote) {
            return false; //todo
        }

        if($action == Votes::TYPE_UP){
            if($vote->upVote <= 0){
                $vote->upVote = 0;
            }else{
                $vote->upVote--;
            }
        }else{
            if($vote->downVote <= 0){
                $vote->downVote = 0;
            }else{
                $vote->downVote--;
            }
        }
        $vote->save();
    }
}
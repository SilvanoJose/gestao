<?php
namespace src\handlers;

use \src\models\User;
use \src\models\UserRelation;
use \src\models\Post;

class PostHandler {

    public static function addPost($idUser, $type, $body ){
        $body = trim($body);

        if(!empty($idUser) && !empty($body)){
            
            Post::insert([
                'id_user' => $idUser, 
                'type' => $type, 
                'created_at' => date('Y-m-d H:i:s'), 
                'body' => $body
            ])->execute();

        }

    }

    public static function getHomeFeed($idUser, $page) {
        $perPage = 3;
        // 1. pegar lista de usuários que EU sigo.
        $userList = UserRelation::select()->where('user_from', $idUser)->get();
        $users = [];
        foreach($userList as $userItem) {
            $users[] = $userItem['user_to'];
        }
        $users[] = $idUser;
       
        // 2. pegar os posts dessa galera ordenado pela data.
       $postList = Post::select()
            ->where('id_user', 'in', $users)
            ->orderBy('created_at', 'desc')
            ->page($page, $perPage)
        ->get();

        $total = Post::select()
            ->where('id_user', 'in', $users)
        ->count();
        $pageCount = ceil($total / $perPage);        

        // 3. transformar o resultado em objetos dos models
        $posts = [];
        foreach($postList as $postItem){
            $newPost = new Post();
            $newPost->id = $postItem['id'];
            $newPost->type = $postItem['type'];
            $newPost->created_at = $postItem['created_at'];
            $newPost->body = $postItem['body'];
            $newPost->mine = false;

            if($postItem['id_user'] == $loggedUserId) {
                $newPost->mine = true;
            }

            // 4. Preencher as informações adicionais no Post
            $newUser = User::select()->where('id', $postItem['id_user'])->one();
            $newPost->user = new User();
            $newPost->user->id = $newUser['id'];
            $newPost->user->name = $newUser['name'];
            $newPost->user->avatar = $newUser['avatar'];   
            
            $newPost->likeCount = 0;
            $newPost->comments = [];
            $newPost->liked = false;
            
            $posts[] = $newPost; 

        }        

        return ['posts' => $posts, 'pageCount' => $pageCount, 'currentPage' => $page];
    }

    public static function getPhotosFrom($idUser){
        $photosData = Post::select()
        ->where('id_user', $idUser)
        ->where('type', 'photo')
    ->get();
    $photos = [];

    foreach($photosData as $photo) {
        $newPost = new Post();
        $newPost->id = $photo['id'];
        $newPost->type = $photo['type'];
        $newPost->created_at = $photo['created_at'];
        $newPost->body = $photo['body'];

        $photos[] = $newPost;
    }
    return $photos;
    }

}
<?php

define('__ROOT__', dirname(__FILE__));
header('Content-Type: text/html; charset=utf-8');

require_once('WordPress/WpPosts.php');
require_once('Portal/Posts.php');
require_once('WordPress/WpUser.php');
require_once('WordPress/WpTerms.php');
require_once('Portal/User.php');
require_once('Portal/SuperCategory.php');
require_once('Portal/Category.php');
require_once('Portal/Relation.php');
require_once('Model.php');

startConversion();

function startConversion() {
    cleanAllData();

    fillUserTable();
    fillCategoryTable();
    fillPostTable();
    fillRelationPostCategory();
}

function fillUserTable() {
    $wpUser = new WpUser();
    $wpUsers = $wpUser->GetAll();
    $wpUserMeta = $wpUser->GetMetaMap();

    $user = new User();
    $user->InsertAll($wpUsers, $wpUserMeta);
}

function fillCategoryTable() {
    $wpTerm = new WpTerms();
    $superCategories = $wpTerm->GetSuperCategories();
    $categories = $wpTerm->GetCategories();

    $superCategoryModel = new SuperCategory();
    $superCategoryModel->InsertAll($superCategories);

    $categoryModel = new Category();
    $categoryModel->InsertAll($categories, $superCategories);
}

function fillPostTable() {
    $wpModel = new WpPosts();

    $wpPosts = $wpModel->GetAllWithMeta();
    $wpContent = $wpModel->GetAllContent();
    $wpPostMatching = $wpModel->GetPostMatching($wpContent);

    $iusModel = new Posts();
    $iusModel->InsertAll($wpPosts, $wpContent, $wpPostMatching);
}

function cleanAllData() {
    $postModel = new Posts();
    $postModel->Truncate();

    $userModel = new User();
    $userModel->Truncate();

    $superCategoryModel = new SuperCategory();
    $superCategoryModel->Truncate();

    $categoryModel = new Category();
    $categoryModel->Truncate();

    $categoryModel = new Relation();
    $categoryModel->Truncate();
}

function fillRelationPostCategory() {
    $wpTerm = new WpTerms();
    $wpRelations = $wpTerm->GetRelations();

    $relation = new Relation();
    $relation->InsertAll($wpRelations);
}
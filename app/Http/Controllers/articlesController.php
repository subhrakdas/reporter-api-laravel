<?php

namespace App\Models;
namespace App\Http\Controllers;

use App\uploads;
use App\uploadFiles;
use App\articles;
use App\medias;
use DB;
use Illuminate\Http\Request;

class articlesController extends Controller
{
    /**
     * Remove ifExists
     * Executes when true flag is present
     */
    public function ifExists(Request $request)
    {
        $existing_article_id = implode($request->only(['ext_article_id']));
        $media_items = json_decode($request->input('attachments'), TRUE);
        
        // Delete existing article from articles table
        $deleteArticle = DB::table('articles')
                                ->where('ext_article_id', $existing_article_id)
                                ->delete();

        // Delete existing media items from medias table
        foreach($media_items["data"] as $row)
            {
            $existing_media_item_id = $row['ext_upload_item_id'];
            
            $deleteMediaItems = DB::table('medias')
                                    ->where('ext_upload_item_id', $existing_media_item_id)
                                    ->delete();
        }
    }
    /**
     * Get values from table
     */
    public function getValues($ext_upload_item_id)
    {
        $getRow = DB::table('upload_files')
                            ->where('ext_upload_item_id', $ext_upload_item_id)
                            ->first();
        return $getRow;
    }

    /**
     * Executes createArticles
     * Checks if retry there
     */
    public function createArticles(Request $request)
    {
        $ext_article_id = implode($request->only(['ext_article_id']));
        $attachments = json_decode($request->input('attachments'), TRUE);
        $retry_create = implode($request->only(['retry']));
        $articleFound = false;

        $articleID = DB::table('articles')
                            ->where('ext_article_id', $ext_article_id)
                            ->value('ext_article_id');

        // Check if rety set to true && articles available
        if($ext_article_id == $articleID && $retry_create == "true")
        {
            $this->ifExists($request);
            $articleFound = false;
        }
        else if ($ext_article_id == $articleID)
        {
            $articleFound = true;
            return response()->json([
                'ext_article_id'     => $articleID,
                'message'           => 'error in create article, article id already present',
            ]);
        }
        
        // Insert to article
        if(!$articleFound)
        {
            $createArticle = new articles;
            $createArticle      -> ext_article_id       = $request->ext_article_id;
            $createArticle      -> title                = $request->title;
            $createArticle      -> headline             = $request->headline;
            $createArticle      -> kicker               = $request->kicker;
            $createArticle      -> caption              = $request->caption;
            $createArticle      -> tags                 = $request->tags;
            $createArticle      -> body                 = $request->body;
            $createArticle      -> declaration          = $request->declaration;
            $createArticle      -> location             = $request->location;
            $createArticle      -> language             = $request->language;
            $createArticle      -> district             = $request->district;
            $createArticle      -> state                = $request->state;
            $createArticle      -> reporter_name        = $request->reporter_name;
            $createArticle      -> reporter_id          = $request->reporter_id;
            $createArticle      -> publish_status       = $request->publish_status;
            $createArticle      -> ingest_status        = $request->ingest_status;
            $createArticle      -> ingest_id            = $request->ingest_id;
            $createArticle      -> save();

            //insert to medias
            $arrmediaItems = array();

            foreach ($attachments["data"] as $row) {
            $medias = new medias;

            // Get row from uploadFiles
            $ext_upload_item_id = $row['ext_upload_item_id'];
            $getUploadedItem = $this->getValues($ext_upload_item_id);
            
            $medias     -> article()->associate($createArticle);
            $medias     -> ext_upload_item_id   = $getUploadedItem->ext_upload_item_id;
            $medias     -> file_name            = $getUploadedItem->file_name;
            $medias     -> file_type            = $getUploadedItem->file_type;
            $medias     -> file_size            = $getUploadedItem->file_size;
            $medias     -> upload_url           = $getUploadedItem->upload_url;

            $medias -> save();

            // Create array from inserted medias
            $arrmediaItems[] = $medias;
            }
        }

        return response()->json([
            'article'       => $createArticle,
            'medias'         => $arrmediaItems,
        ]);
    }

    /**
     * Executes getArticle
     * Returns specific article from database
     */
    public function getArticle(Request $request)
    {
        $ext_article_id = implode($request->only(['ext_article_id']));

        $article = DB::table('articles')->where('ext_article_id', $ext_article_id)->first();

        $ID = $article->id;

        $medias = articles::find($ID)->mediaItems;
        
        return response()->json([
            'article'       => $article,
            'attachments'   => $medias,
        ]);
    }
}

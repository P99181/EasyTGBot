<?php

if($TGBot->settings['adminMySQL']){
        $TGBot->mdb->query("CREATE TABLE IF NOT EXISTS EasyTGBot(
            chat_id BIGINT(11) NOT NULL,
            first_name TEXT(50) , 
            last_name TEXT(50),
            username TEXT(50), 
            action TEXT(50) NOT NULL,
            title TEXT(50),
            type TEXT(50),
            to_update TEXT(50)
            );");
        $la = $TGBot->mdb->prepare("SELECT * FROM EasyTGBot WHERE chat_id = ?");
        $la->execute([$TGBot->chat_id]);
        $la = $la->fetch(\PDO::FETCH_ASSOC);
        if($TGBot->chat_id != $la['chat_id']){
            $insertprep = $TGBot->mdb->prepare("INSERT INTO EasyTGBot (chat_id, first_name, last_name, username, action, title, type, to_update) VALUES (?,?,?,?,?,?,?,?)");
            if($TGBot->type == 'supergroup' or $TGBot->type == 'group' or $TGBot->type == 'channel'){
                $insertprep->execute([$TGBot->chat_id, NULL, NULL, NULL, 'none', $TGBot->title, $TGBot->type, true]);
            }else{
                $insertprep->execute([$TGBot->chat_id, $TGBot->first_name, $TGBot->last_name, $TGBot->username, 'none', NULL, $TGBot->type, true]);
            }
        }else{
            if($la['to_update']){
                if(in_array($TGBot->type, ['supergroup','group','channel'])){
                    $update = $TGBot->mdb->prepare("UPDATE EasyTGBot SET title=?, type=?, to_update=? WHERE chat_id=?"); 
                    $update->execute([$TGBot->title, $TGBot->type, true, $TGBot->chat_id]);
                }else{
                    $update = $TGBot->mdb->prepare("UPDATE EasyTGBot SET first_name=?, last_name=?, username=?, type=?, to_update=? WHERE chat_id=?"); 
                    $update->execute([$TGBot->first_name, $TGBot->last_name, $TGBot->username, $TGBot->type, true, $TGBot->chat_id]);
                }
            }
        }

 
if($TGBot->text == '/admin' and $TGBot->botAdmin() or $TGBot->cbdata_text == '/admin' and $TGBot->botAdmin()){
        $buttons[] = array(
            array(
                'text' => 'Users Number',
                'callback_data' => '/unumber'
            )
        );
        $buttons[] = array(
            array(
                'text' => 'Global Post',
                'callback_data' => '/globalpost'
            )
        );
        $text = "Ok! Perfect, now, select what to do.";
        if($TGBot->cbdata){
            $TGBot->editMessage($TGBot->chat_id, $TGBot->message_id, $text, $buttons);
        }else{
            $TGBot->sendMessage($TGBot->chat_id, $text, $buttons);
        }
    }

    if($TGBot->cbdata_text == '/unumber'and $TGBot->botAdmin()){
        $buttons[] = array(
            array(
                'text' => 'Admin Panel',
                'callback_data' => '/admin'
            )
        );
        $private = $TGBot->mdb->prepare("SELECT * FROM EasyTGBot WHERE type=?");
        $private->execute(['private']);
        $groups = $TGBot->mdb->prepare("SELECT * FROM EasyTGBot WHERE type=?");
        $groups->execute(['group']);
        $supergroup = $TGBot->mdb->prepare("SELECT * FROM EasyTGBot WHERE type=?");
        $supergroup->execute(['supergroup']);
        $channel = $TGBot->mdb->prepare("SELECT * FROM EasyTGBot WHERE type=?");
        $channel->execute(['channel']);
        $TGBot->editMessage($TGBot->chat_id, $TGBot->message_id, "<b>Here is the list of all users</b> \n👤 Private chat: ".$private->rowCount()."\n👥 Groups: ".$groups->rowCount()."\n👥 Supergroups: ".$supergroup->rowCount()."\n🗣 Channels: ".$channel->rowCount(), $buttons);
    }

    if($TGBot->cbdata_text == '/globalpost'and $TGBot->botAdmin()){
        $buttons[] = array(
            array(
                'text' => 'Only Groups',
                'callback_data' => '/gpost g'
            ),
            array(
                'text' => 'Only Users',
                'callback_data' => '/gpost u'
            )
        );
        $buttons[] = array(
            array(
                'text' => 'Both',
                'callback_data' => '/gpost b'
            )
        );
        $buttons[] = array(
            array(
                'text' => 'Cancel operation',
                'callback_data' => '/gpost cancel'
            )
        );
        $TGBot->editMessage($TGBot->chat_id, $TGBot->message_id, "Ok! Perfect, now, select who must receive the message.", $buttons);
    }
    if(strpos($TGBot->cbdata_text, '/gpost')===0and $TGBot->botAdmin()){
        $t = explode(' ', $TGBot->cbdata_text);
        if($t[1] == 'cancel'){
            $TGBot->deleteMessage($TGBot->chat_id, $TGBot->message_id);
            $update = $TGBot->mdb->prepare("UPDATE EasyTGBot SET action=?, to_update=? WHERE chat_id=?"); 
            $update->execute(['none', true, $TGBot->chat_id]);
        }elseif($t[1] == 'media' or $t[1] == 'sticker' or $t[1] == 'text'){
            $update = $TGBot->mdb->prepare("UPDATE EasyTGBot SET action=?, to_update=? WHERE chat_id=?"); 
            $update->execute(['post.'.$t[1].'_'.$t[2], false, $TGBot->chat_id]);
            $buttons[] = array(
                array(
                    'text' => 'Cancel operation',
                    'callback_data' => '/gpost cancel'
                )
            );
            $TGBot->editMessage($TGBot->chat_id, $TGBot->message_id, 'Ok, now send me the '.$t[1].' and reply to the message with /send.', $buttons);
        }else{
            $buttons[] = array(
                array(
                    'text' => '💾 Media',
                    'callback_data' => '/gpost media '.$t[1]
                )
            );
            $buttons[] = array(
                array(
                    'text' => 'Text Message',
                    'callback_data' => '/gpost text '.$t[1]
                )
            );
            $buttons[] = array(
                array(
                    'text' => 'Cancel operation',
                    'callback_data' => '/gpost cancel'
                )
            );
            $TGBot->editMessage($TGBot->chat_id, $TGBot->message_id, 'Ok, now select wich type of message do you want send.', $buttons);
        }
    }
    if($TGBot->text == '/send' and isset($TGBot->reply_to_message)and $TGBot->botAdmin()){
        $select = $TGBot->mdb->prepare("SELECT * FROM EasyTGBot WHERE chat_id=?");
        $select->execute([$TGBot->chat_id]);
        $select = $select->fetch(PDO::FETCH_ASSOC); 
        $ex = explode("_", str_replace('post.', '', $select['action']));
        if($ex[1] == 'g'){
            $TGBot->sendMessage($TGBot->chat_id, "I'm sending the media in all groups.");
            $fetchAll = $TGBot->mdb->prepare("SELECT * FROM EasyTGBot WHERE type=?");
            $fetchAll->execute(['supergroup']);
            $fetchAll = $fetchAll->fetchAll(PDO::FETCH_ASSOC);
            foreach($fetchAll as $fetch){
                if($TGBot->reply_photo){
                    $TGBot->sendPhoto($fetch['chat_id'], $TGBot->reply_photo_file_id, $TGBot->reply_photo_caption);
                }
                if($TGBot->reply_to_message_text){
                    $TGBot->sendMessage($fetch['chat_id'], $TGBot->reply_to_message_text);
                }
                if($TGBot->reply_video){
                    $TGBot->sendVideo($fetch['chat_id'], $TGBot->reply_video_file_id, $TGBot->reply_video_caption);
                }
                if($TGBot->reply_document){
                    $TGBot->sendDocument($fetch['chat_id'], $TGBot->reply_document_file_id, $TGBot->reply_document_caption);
                }
            }
            $fetchAll = $TGBot->mdb->prepare("SELECT * FROM EasyTGBot WHERE type=?");
            $fetchAll->execute(['group']);
            $fetchAll = $fetchAll->fetchAll(PDO::FETCH_ASSOC);
            foreach($fetchAll as $fetch){
                if($TGBot->reply_photo){
                    $TGBot->sendPhoto($fetch['chat_id'], $TGBot->reply_photo_file_id, $TGBot->reply_photo_caption);
                }
                if($TGBot->reply_to_message_text){
                    $TGBot->sendMessage($fetch['chat_id'], $TGBot->reply_to_message_text);
                }
                if($TGBot->reply_video){
                    $TGBot->sendVideo($fetch['chat_id'], $TGBot->reply_video_file_id, $TGBot->reply_video_caption);
                }
                if($TGBot->reply_document){
                    $TGBot->sendDocument($fetch['chat_id'], $TGBot->reply_document_file_id, $TGBot->reply_document_caption);
                }
            }
        }
        if($ex[1] == 'u'){
            $TGBot->sendMessage($TGBot->chat_id, "I'm sending the media to all users.");
            $fetchAll = $TGBot->mdb->prepare("SELECT * FROM EasyTGBot WHERE type=?");
            $fetchAll->execute(['private']);
            $fetchAll = $fetchAll->fetchAll(PDO::FETCH_ASSOC);
            foreach($fetchAll as $fetch){
                if($TGBot->reply_photo){
                    $TGBot->sendPhoto($fetch['chat_id'], $TGBot->reply_photo_file_id, $TGBot->reply_photo_caption);
                }
                if($TGBot->reply_to_message_text){
                    $TGBot->sendMessage($fetch['chat_id'], $TGBot->reply_to_message_text);
                }
                if($TGBot->reply_video){
                    $TGBot->sendVideo($fetch['chat_id'], $TGBot->reply_video_file_id, $TGBot->reply_video_caption);
                }
                if($TGBot->reply_document){
                    $TGBot->sendDocument($fetch['chat_id'], $TGBot->reply_document_file_id, $TGBot->reply_document_caption);
                }
            }
        }
        if($ex[1] == 'b'){
            $TGBot->sendMessage($TGBot->chat_id, "I'm sending the media in all groups and to all users.");
            $fetchAll = $TGBot->mdb->prepare("SELECT * FROM EasyTGBot WHERE type=?");
            $fetchAll->execute(['supergroup']);
            $fetchAll = $fetchAll->fetchAll(PDO::FETCH_ASSOC);
            foreach($fetchAll as $fetch){
                if($TGBot->reply_photo){
                    $TGBot->sendPhoto($fetch['chat_id'], $TGBot->reply_photo_file_id, $TGBot->reply_photo_caption);
                }
                if($TGBot->reply_to_message_text){
                    $TGBot->sendMessage($fetch['chat_id'], $TGBot->reply_to_message_text);
                }
                if($TGBot->reply_video){
                    $TGBot->sendVideo($fetch['chat_id'], $TGBot->reply_video_file_id, $TGBot->reply_video_caption);
                }
                if($TGBot->reply_document){
                    $TGBot->sendDocument($fetch['chat_id'], $TGBot->reply_document_file_id, $TGBot->reply_document_caption);
                }
            }
            $fetchAll = $TGBot->mdb->prepare("SELECT * FROM EasyTGBot WHERE type=?");
            $fetchAll->execute(['group']);
            $fetchAll = $fetchAll->fetchAll(PDO::FETCH_ASSOC);
            foreach($fetchAll as $fetch){
                if($TGBot->reply_photo){
                    $TGBot->sendPhoto($fetch['chat_id'], $TGBot->reply_photo_file_id, $TGBot->reply_photo_caption);
                }
                if($TGBot->reply_to_message_text){
                    $TGBot->sendMessage($fetch['chat_id'], $TGBot->reply_to_message_text);
                }
                if($TGBot->reply_video){
                    $TGBot->sendVideo($fetch['chat_id'], $TGBot->reply_video_file_id, $TGBot->reply_video_caption);
                }
                if($TGBot->reply_document){
                    $TGBot->sendDocument($fetch['chat_id'], $TGBot->reply_document_file_id, $TGBot->reply_document_caption);
                }
            }
            $fetchAll = $TGBot->mdb->prepare("SELECT * FROM EasyTGBot WHERE type=?");
            $fetchAll->execute(['private']);
            $fetchAll = $fetchAll->fetchAll(PDO::FETCH_ASSOC);
            foreach($fetchAll as $fetch){
                if($TGBot->reply_photo){
                    $TGBot->sendPhoto($fetch['chat_id'], $TGBot->reply_photo_file_id, $TGBot->reply_photo_caption);
                }
                if($TGBot->reply_to_message_text){
                    $TGBot->sendMessage($fetch['chat_id'], $TGBot->reply_to_message_text);
                }
                if($TGBot->reply_video){
                    $TGBot->sendVideo($fetch['chat_id'], $TGBot->reply_video_file_id, $TGBot->reply_video_caption);
                }
                if($TGBot->reply_document){
                    $TGBot->sendDocument($fetch['chat_id'], $TGBot->reply_document_file_id, $TGBot->reply_document_caption);
                }
            }
        }
        $update = $TGBot->mdb->prepare("UPDATE EasyTGBot SET action=?, to_update=? WHERE chat_id=?"); 
        $update->execute(['none', true, $TGBot->chat_id]);
        $TGBot->sendMessage($TGBot->chat_id, "Done!");
    }
}

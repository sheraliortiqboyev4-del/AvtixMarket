<?php

class Begzod {
    private $update;

    public function __construct() {
        $this->update = json_decode(file_get_contents('php://input'));
    }

    public function update() {
        return $this->update;
    }

    public function sendVideoNote($params) {
        return $this->request('sendVideoNote', $params);
    }
    public function sendMessage($params) {
        return $this->request('sendMessage', $params);
    }
    public function sendAudio($params) {
        return $this->request('sendAudio', $params);
    }
    public function sendVideo($params) {
        return $this->request('sendVideo', $params);
    }
    public function sendMediaGroup($params) {
        return $this->request('sendMediaGroup', $params);
    }
    public function promoteChatMember($params) {
        return $this->request('promoteChatMember', $params);
    }
    public function getBotId($params) {
        return $this->request('getBotId', $params);
    }
     public function sendVoice($params) {
        return $this->request('sendVoice', $params);
    }
     public function answerPreCheckoutQuery($params) {
        return $this->request('answerPreCheckoutQuery', $params);
    }
    public function sendInvoice($params) {
        return $this->request('sendInvoice', $params);
    }
     public function getChatMemberCount($params) {
        return $this->request('getChatMemberCount', $params);
    }
     public function getChatMembersCount($params) {
        return $this->request('getChatMembersCount', $params);
    }
    public function sendPhoto($params) {
        return $this->request('sendPhoto', $params);
    }
    public function sendSticker($params) {
        return $this->request('sendSticker', $params);
    }
    public function getChat($params) {
        return $this->request('getChat', $params);
    }
    public function getChatMember($params) {
        return $this->request('getChatMember', $params);
    }
    public function getFile($params) {
        return $this->request('getFile', $params);
    }

    public function editMessageText($params) {
        return $this->request('editMessageText', $params);
    }
    
        public function editMessageReplyMarkup($params) {
        return $this->request('editMessageReplyMarkup', $params);
    }
    public function deleteMessage($params) {
        return $this->request('deleteMessage', $params);
    }
    public function answerCallbackQuery($params) {
        return $this->request('answerCallbackQuery', $params);
    }
    public function answerInlineQuery($params) {
        return $this->request('answerInlineQuery', $params);
    }
    public function copyMessage($params) {
        return $this->request('copyMessage', $params);
    }
    public function editMessageCaption($params) {
        return $this->request('editMessageCaption', $params);
    }
    public function getResult($params) {
        return $this->request('getResult', $params);
    }
    public function getTitle($params) {
        return $this->request('getTitle', $params);
    }
    public function getCustomEmojiStickers($params) {
        return $this->request('getCustomEmojiStickers', $params);
    }
    public function setWebhook($params) {
        return $this->request('setWebhook', $params);
    }
  
  
    private function request($method, $params = []) {
        $url = API_URL . $method;
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response);
    }
}

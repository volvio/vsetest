<?php

class ModelSite {

    private $db;

    function __construct($db) {
        $this->db = $db;
    }
/**
 * Найти id сайта по url сайта
 * @param type $url
 * @return boolean
 */
    public function getSiteIdByUrl($url) {
        $pre = $this->db->prepare("SELECT `id` FROM  `site` WHERE `url`=:url");
        $pre->bindParam(':url', $url);
        $pre->execute();
        $res = $pre->fetch(PDO::FETCH_ASSOC);

        if ($res) {
            return $res["id"];
        }
        return FALSE;
    }
/**
 * Добавить новый сайт
 * @param type $url
 * @return boolean
 */
    public function addSite($url) {
        if (strlen($url) > 3) {
            $pre = $this->db->prepare("INSERT INTO `site` (`url`) VALUES (:url)");
            $pre->bindParam(':url', $url);
            $pre->execute();
            return TRUE;
        }
        return FALSE;
    }

}

<?php

class ModelProduct {

    private $db;

    function __construct($db) {
        $this->db = $db;
    }
/**
 * Пролучить продукт по id продукта и по id сайта
 * @param type $product_id
 * @param type $site_id
 * @return boolean
 */
    public function getProduct($product_id, $site_id) {
        $SQL = "SELECT * FROM  `products` WHERE `product_id`=" . (int) $product_id . " AND site_id =" . (int) $site_id;
        $result = $this->db->query($SQL);
        if ($result) {
            $res = $result->fetch(PDO::FETCH_ASSOC);
            return $res;
        }
        return FALSE;
    }
/**
 * Обновить продукт
 * @param type $product_id
 * @param type $site_id
 * @param type $url
 * @return boolean
 */
    public function updateProduct($product_id, $site_id, $url) {
        if ($product_id > 0 && $site_id > 0) {
            $pre = $this->db->prepare("UPDATE `products` SET `url`=:url WHERE `site_id`=:site_id AND `product_id` = :product_id");
            $pre->bindParam(':url', $url);
            $pre->bindParam(':product_id', $product_id);
            $pre->bindParam(':site_id', $site_id);
            $pre->execute();
            return TRUE;
        }
        return FALSE;
    }
/**
 * Добавить продукт
 * @param type $product_id
 * @param type $site_id
 * @param type $url
 * @return boolean
 */
    public function addProduct($product_id, $site_id, $url) {
        if ($product_id > 0 && $site_id > 0) {
            $pre = $this->db->prepare("INSERT INTO `products` (`product_id`,`site_id`,`url`) VALUES (:product_id,:site_id,:url)");
            $pre->bindParam(':url', $url);
            $pre->bindParam(':product_id', $product_id);
            $pre->bindParam(':site_id', $site_id);
            $pre->execute();
            return TRUE;
        }
        return FALSE;
    }

}

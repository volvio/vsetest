<?php
class ModelPromotion {

    private $db;

    function __construct($db) {
        $this->db = $db;
    }
/**
 * Возвращает список акций
 * @param type $start лимиты
 * @param type $limit
 * @return список акций
 */
    public function getPromotions($start = 0, $limit = 100) {
        $SQL = "SELECT * FROM  `promotions` limit " . (int) $start . "," . (int) $limit;
        $result = $this->db->query($SQL);
        if ($result) {
            $res = $result->fetchAll(PDO::FETCH_ASSOC);
            return $res;
        }
        return FALSE;
    }

    public function getCountPromotions($start = 0, $limit = 100) {
        $SQL = "SELECT count(id) as cnt FROM  `promotions` limit " . (int) $start . "," . (int) $limit;
        $result = $this->db->query($SQL);
        if ($result) {
            $res = $result->fetch(PDO::FETCH_ASSOC);
            return $res["cnt"];
        }
        return FALSE;
    }
/**
 * Найти совпадение по полям $title и $description
 * @param type $title
 * @param type $description
 * @return id совпадающей записи
 */
    public function findPromotion($title, $description) {

        $pre = $this->db->prepare("SELECT `id` FROM  `promotions` WHERE `title`=:title AND `description`=:description");
        $pre->bindParam(':title', $title);
        $pre->bindParam(':description', $description);
        $pre->execute();
        $res = $pre->fetch(PDO::FETCH_ASSOC);
        if ($res) {
            return $res["id"];
        }

        return FALSE;
    }
/**
 * Добавление новой акции
 * @param type $param массив параметров для акции
 * @return id вставленой записи
 */
    public function addPromotion($param = "") {
        if (is_array($param) && count($param) > 0) {
            $pre = $this->db->prepare("INSERT INTO `promotions` (`title`,`description`,`url`,`image`,`date_start`, `date_end`) VALUES (:title,:description,:url,:image,:date_start, :date_end)");
            $pre->bindParam(':title', $param["title"]);
            $pre->bindParam(':description', $param["description"]);
            $pre->bindParam(':url', $param["url"]);
            $pre->bindParam(':image', $param["image"]);
            $pre->bindParam(':date_start', $param["date_start"]);
            $pre->bindParam(':date_end', $param["date_end"]);
            $pre->execute();
            return $this->db->lastInsertId();
        }
        return FALSE;
    }
/**
 * Обновление записи акции
 * @param type $param
 * @return boolean
 */
    public function updatePromotion($param = "") {
        if (is_array($param) && count($param) > 0) {
            $pre = $this->db->prepare("UPDATE `promotions` SET (`title` =:title,`description`=:description,`url`=:url,`image`=:image,`date_start`=:date_start, `date_end`=:date_end WHERE `id` = :id");
            $pre->bindParam(':id', $param["id"]);
            $pre->bindParam(':title', $param["title"]);
            $pre->bindParam(':description', $param["description"]);
            $pre->bindParam(':url', $param["url"]);
            $pre->bindParam(':image', $param["image"]);
            $pre->bindParam(':date_start', $param["date_start"]);
            $pre->bindParam(':date_end', $param["date_end"]);
            $pre->execute();
            return TRUE;
        }
        return FALSE;
    }
/**
 * Делает связь продукта и связки
 * @param type $product_id
 * @param type $promotion_id
 * @param type $site_id
 * @return boolean
 */
    public function addProductToPromotion($product_id, $promotion_id, $site_id) {
        if ($product_id > 0 && $promotion_id > 0 && $site_id > 0) {
            $pre = $this->db->prepare("INSERT INTO `product_to_promotion` (`product_id`,`promotion_id`,`site_id`) VALUES (:product_id,:promotion_id,:site_id)");
            $pre->bindParam(':product_id', $product_id);
            $pre->bindParam(':promotion_id', $promotion_id);
            $pre->bindParam(':site_id', $site_id);
            $pre->execute();
            return TRUE;
        }
        return FALSE;
    }
/**
 * Удаление связи продукт и акции
 * @param type $product_id
 * @param type $promotion_id
 * @param type $site_id
 * @return boolean
 */
    public function delProductToPromotion($product_id, $promotion_id, $site_id) {
        if ($product_id > 0 && $promotion_id > 0 && $site_id > 0) {
            $pre = $this->db->prepare("DELETE FROM `product_to_promotion` WHERE `product_id`=:product_id AND `promotion_id`=:promotion_id AND `site_id`=:site_id");
            $pre->bindParam(':product_id', $product_id);
            $pre->bindParam(':promotion_id', $promotion_id);
            $pre->bindParam(':site_id', $site_id);
            $pre->execute();
            return TRUE;
        }
        return FALSE;
    }
/**
 * Подсчет количества прродуктов для акции
 * @param type $promotion_id
 * @return boolean
 */
    public function getCountProductToPromotion($promotion_id) {
        $SQL = "SELECT count(`product_id`) as cnt FROM  `product_to_promotion` WHERE  `promotion_id` =" . (int) $promotion_id;
        $result = $this->db->query($SQL);
        if ($result) {
            $res = $result->fetch(PDO::FETCH_ASSOC);
            if ($res["cnt"] > 0) {
                return $res["cnt"];
            }
        }
        return FALSE;
    }

}

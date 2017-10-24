<?php
class Validate {

    private $error;
    private $key;
    private $param;

    function __construct() {
        $this->error = "";
    }

    public function getError() {
        return $this->error;
    }

    public function clearError() {
        $this->error = "";
    }
/**
 * Основная функция проверки данных
 * @param type $param - один элемент sales
 * @param type $key - указатель позиции элемента sales
 * @return boolean
 */
    public function validateAll($param, $key) {
        $this->key = $key;
        $this->param = $param;
        $t = $this->validateTitle($param);
        $d = $this->validateDescription($param);
        $u = $this->validateUrl($param);
        $i = $this->validateImage($param);
        $date = $this->validateDate($param);
        $p = $this->validateProducts($param);
        if ($t && $d && $u && $i && $date && $p) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    public function validateTitle() {
        if (isset($this->param->title)) {
            $len = mb_strlen((string) $this->param->title);
            if ($len > 3 && $len < 151) {
                return TRUE;
            } else {
                $this->error[] = "Для позиции: $this->key, Длина строки  title должна быть от 3 до 150 символов ";
                return FALSE;
            }
        } else {
            $this->error[] = "Для позиции: $this->key, отсуствует обязательный тег title  ";
            return FALSE;
        }
        return TRUE;
    }

    public function validateDescription() {
        if (isset($this->param->description)) {
            $len = mb_strlen((string) $this->param->description);
            if ($len > 500) {
                $this->error[] = "Для позиции: $this->key, поле  description превышает допустимую длинну 500 символов   ";
                return FALSE;
            }
        }

        return TRUE;
    }

    public function validateUrl() {
        if (isset($this->param->url)) {
            if (filter_var((string) $this->param->url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) === FALSE) {
                $this->error[] = "Для позиции: $this->key, поле  url не верный формат ссылки";
                return FALSE;
            }
        }
        return TRUE;
    }

    public function validateImage() {
        if (isset($this->param->image)) {
            if (filter_var((string) $this->param->image, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) === FALSE) {
                $this->error[] = "Для позиции: $this->key, поле  image не верный формат ссылки";
                return FALSE;
            }
        }
        return TRUE;
    }

    public function validateDate() {
        $res = TRUE;
        if (isset($this->param->date_start)) {

            $date = DateTime::createFromFormat('Y-m-d', (string) $this->param->date_start);
            if ($date === FALSE) {
                $this->error[] = "Для позиции: $this->key, поле  date_start не верный формат даты, требуется ГГГГ-ММ-ДД ";
                $res = FALSE;
            }
        } else {
            $this->error[] = "Для позиции: $this->key,поле date_start обязательно к заполнению ";
            $res = FALSE;
        }
        if (isset($this->param->date_end)) {
            $date = DateTime::createFromFormat('Y-m-d', (string) $this->param->date_end);
            if ($date === FALSE) {
                $this->error[] = "Для позиции: $this->key, поле  date_end не верный формат даты, требуется ГГГГ-ММ-ДД ";
                $res = FALSE;
            }
        } else {
            $this->error[] = "Для позиции: $this->key,поле date_end  обязательно к заполнению";
            $res = FALSE;
        }
        return $res;
    }

    public function validateProducts() {
        $res = TRUE;
        if (isset($this->param->products)) {
            $products = $this->param->products["0"];
            if ($products) {
                foreach ($products as $key => $prod) {
                    if (isset($prod["id"])) {
                        $prod_id = (string) $prod["id"];
                        $prod_url = (string) $prod;
                        $len = mb_strlen($prod_id);

                        if ($len > 20) {
                            $this->error[] = "Для позиции: $this->key, поле product, запись $key  атрибут id должен быть длинной меньше 20 символов ";
                            $res = FALSE;
                        }

                        if (filter_var($prod_url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) === FALSE) {
                            $this->error[] = "Для позиции: $this->key, поле product, запись $key  указана не верная ссылка на продукт ";
                            $res = FALSE;
                        }
                    } else {
                        $this->error[] = "Для позиции: $this->key, поле product, запись $key  обязательно должны быть указан атрибут id ";
                        $res = FALSE;
                    }
                }
            } else {
                $this->error[] = "Для позиции: $this->key, поле products обязательно должны быть указаны продукты, для которых действует акция ";
                $res = FALSE;
            }
        } else {
            $this->error[] = "Для позиции: $this->key, поле products обязательно к заполнению ";
            $res = FALSE;
        }
        return $res;
    }

}

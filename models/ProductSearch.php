<?php

namespace app\models;


use yii\elasticsearch\ActiveRecord;


class ProductSearch extends ActiveRecord {


	public function attributes() {
		return ["productid", "title", "descr"];
	}

	public static function index() {
		return "test";
	}

	public static function type() {

		//	return parent::type(); // TODO: Change the autogenerated stub
		return "product";
	}


}
<?php
class ArticleFixture extends CakeTestFixture {
    public $useDbConfig = 'test';
    public $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'name' => array('type' => 'string', 'length' => 64),
        'created' => 'datetime',
        'modified' => 'datetime'
    );
    public $records = array(
        array('id' => 1, 'name' => 'Bar', 'created' => '2012-01-02 00:00:00', 'modified' => '2012-01-02 00:00:00')
    );
}

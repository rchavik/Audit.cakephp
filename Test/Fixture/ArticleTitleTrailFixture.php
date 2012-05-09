<?php
class ArticleTitleTrailFixture extends CakeFixture {
    public $useDbConfig = 'test';
    public $fields = array(
        'trail_id' => array('type' => 'integer', 'key' => 'primary'),
        'trail_created' => 'datetime',
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'name' => array('type' => 'string', 'length' => 64),
        'created' => 'datetime',
        'modified' => 'datetime'
    );
    public $records = array(
        array('trail_id' => 1, 'trail_created' => '2012-01-01 00:00:00', 'id' => 1, 'name' => 'Foo', 'created' => '2012-01-01 00:00:00', 'modified' => '2012-01-01 00:00:00'),
        array('trail_id' => 2, 'trail_created' => '2012-01-02 00:00:00', 'id' => 1, 'name' => 'Bar', 'created' => '2012-01-02 00:00:00', 'modified' => '2012-01-02 00:00:00')
    );
}

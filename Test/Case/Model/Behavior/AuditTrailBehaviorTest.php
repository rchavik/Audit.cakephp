<?php
require_once __DIR__ . '/../../Lib/TestConstraintArrayHas.php';
class Article extends CakeTestModel {
    public function read() {
        $data = $this->data;
        $data['Article']['id'] = 1;
        return $data;
    }
}

class ArticleAuditTrail { }

class AuditTrailBehaviorTest extends CakeTestCase {
    public $fixtures = array('plugin.audit.article');
    public $articles;

    public function setUp() {
        parent::setUp();
        App::uses('AuditTrailBehavior', 'Audit.Model/Behavior');
    }

    public function testSavesNewTrailModelWhenSavingNewModel() {
        $fields = array('title');
        $article = new Article;
        $article->data = array(
            'Article' => array(
                'title' => 'Insert Type Test'
            )
        );
        $trailModel = $this->getMock('ArticleAuditTrail', array('save'));
        $trailModel->expects($this->once())->method('save')->with(
            $this->logicalAnd(
                new Test_Constraint_ArrayHas($this->equalTo(null), 'trail_id'),
                $this->arrayHasKey('trail_created'),
                new Test_Constraint_ArrayHas($this->equalTo(null), 'trail_created_by'),
                new Test_Constraint_ArrayHas($this->equalTo(1), 'id'),
                new Test_Constraint_ArrayHas($this->equalTo('Insert Type Test'), 'title')
            )
        );
        $auditTrailBehavior = new AuditTrailBehavior;
        $auditTrailBehavior->setup($article, compact('fields', 'trailModel'));
        $auditTrailBehavior->beforeSave($article);
        $auditTrailBehavior->afterSave($article, true);
    }

    public function testTracksMultipleFields() {
        $fields = array('title','track_id');
        $article = new Article;
        $article->data = array(
            'Article' => array(
                'title' => 'Insert Type Test',
                'track_id' => 1
            )
        );
        $trailModel = $this->getMock('ArticleAuditTrail', array('save'));
        $trailModel->expects($this->once())->method('save')->with(
            $this->logicalAnd(
                new Test_Constraint_ArrayHas($this->equalTo(null), 'trail_id'),
                $this->arrayHasKey('trail_created'),
                new Test_Constraint_ArrayHas($this->equalTo(null), 'trail_created_by'),
                new Test_Constraint_ArrayHas($this->equalTo(1), 'id'),
                new Test_Constraint_ArrayHas($this->equalTo(1), 'track_id'),
                new Test_Constraint_ArrayHas($this->equalTo('Insert Type Test'), 'title')
            )
        );
        $auditTrailBehavior = new AuditTrailBehavior;
        $auditTrailBehavior->setup($article, compact('fields', 'trailModel'));
        $auditTrailBehavior->beforeSave($article);
        $auditTrailBehavior->afterSave($article, true);
    }

    public function testSetsCreatedByWhenCreatedByDataSet() {
        $fields = array('title');
        $article = new Article;
        $article->data = array(
            'Article' => array(
                'title' => 'Created By Test',
                'created_by' => 42
            )
        );
        $trailModel = $this->getMock('ArticleAuditTrail', array('save'));
        $trailModel->expects($this->once())->method('save')->with(
            $this->logicalAnd(
                new Test_Constraint_ArrayHas($this->equalTo(null), 'trail_id'),
                $this->arrayHasKey('trail_created'),
                new Test_Constraint_ArrayHas($this->equalTo(42), 'trail_created_by'),
                new Test_Constraint_ArrayHas($this->equalTo(1), 'id'),
                new Test_Constraint_ArrayHas($this->equalTo('Created By Test'), 'title')
            )
        );
        $auditTrailBehavior = new AuditTrailBehavior;
        $auditTrailBehavior->setup($article, compact('fields', 'trailModel'));
        $auditTrailBehavior->beforeSave($article);
        $auditTrailBehavior->afterSave($article, true);
    }
}


<?php
require_once __DIR__ . '/../../Lib/TestConstraintArrayHas.php';
class ArticleMock extends CakeTestModel {
    public function read() {
        $data = $this->data;
        $data['ArticleMock']['id'] = 1;
        return $data;
    }
}

class PostMock extends CakeTestModel {
    public $id = 1;
    public function read() {
        return array(
            'PostMock' => array(
                'id' => 1,
                'title' => 'Delete Type Test'
            )
        );
    }
}

class AuditRevisionModelMock { }

class AuditRevisionBehaviorTest extends CakeTestCase {
    public function setUp() {
        parent::setUp();
        App::uses('AuditRevisionBehavior', 'Audit.Model/Behavior');
    }

    public function testSavesNewRevisionModelWithInsertType() {
        $article = new ArticleMock;
        $article->data = array(
            'ArticleMock' => array(
                'title' => 'Insert Type Test'
            )
        );
        $revisionModel = $this->getMock('AuditRevisionModelMock', array('save'));
        $revisionModel->expects($this->once())->method('save')->with(
            $this->logicalAnd(
                new Test_Constraint_ArrayHas($this->equalTo(null), 'revision_id'),
                $this->arrayHasKey('revision_created'),
                new Test_Constraint_ArrayHas($this->equalTo('create'), 'revision_type'),
                new Test_Constraint_ArrayHas($this->equalTo(1), 'id'),
                new Test_Constraint_ArrayHas($this->equalTo('Insert Type Test'), 'title')
            )
        );
        $auditRevisionBehavior = new AuditRevisionBehavior;
        $auditRevisionBehavior->setup($article, compact('revisionModel'));
        $auditRevisionBehavior->beforeSave($article);
        $auditRevisionBehavior->afterSave($article, true);
    }

    public function testSavesNewRevisionModelWithUpdateType() {
        $article = new ArticleMock;
        $article->data = array(
            'ArticleMock' => array(
                'id' => 1,
                'title' => 'Insert Type Test Update'
            )
        );
        $revisionModel = $this->getMock('AuditRevisionModelMock', array('save'));
        $revisionModel->expects($this->once())->method('save')->with(
            $this->logicalAnd(
                new Test_Constraint_ArrayHas($this->equalTo(null), 'revision_id'),
                $this->arrayHasKey('revision_created'),
                new Test_Constraint_ArrayHas($this->equalTo('update'), 'revision_type'),
                new Test_Constraint_ArrayHas($this->equalTo(1), 'id'),
                new Test_Constraint_ArrayHas($this->equalTo('Insert Type Test Update'), 'title')
            )
        );
        $auditRevisionBehavior = new AuditRevisionBehavior;
        $auditRevisionBehavior->setup($article, compact('revisionModel'));
        $auditRevisionBehavior->beforeSave($article);
        $auditRevisionBehavior->afterSave($article, true);
    }

    public function testSavesNewRevisionModelWithDeleteType() {
        $post = new PostMock;
        $post->id = 1;
        $revisionModel = $this->getMock('AuditRevisionModelMock', array('save'));
        $revisionModel->expects($this->once())->method('save')->with(
            $this->logicalAnd(
                new Test_Constraint_ArrayHas($this->equalTo(null), 'revision_id'),
                $this->arrayHasKey('revision_created'),
                new Test_Constraint_ArrayHas($this->equalTo('delete'), 'revision_type'),
                new Test_Constraint_ArrayHas($this->equalTo(1), 'id'),
                new Test_Constraint_ArrayHas($this->equalTo('Delete Type Test'), 'title')
            )
        );
        $auditRevisionBehavior = new AuditRevisionBehavior;
        $auditRevisionBehavior->setup($post, compact('revisionModel'));
        $auditRevisionBehavior->beforeDelete($post);
        $auditRevisionBehavior->afterDelete($post);
    }
}

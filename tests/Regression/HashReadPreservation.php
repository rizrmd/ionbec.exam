<?php
require __DIR__.'/../../vendor/autoload.php';$app=require __DIR__.'/../../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if(!app()->environment('testing'))throw new RuntimeException('Isolated testing database required.');
$db=Illuminate\Support\Facades\DB::connection();$db->beginTransaction();
try {
 foreach(['historical-stored-hash',null] as $hash){
  $id=$db->selectOne("INSERT INTO exams(code,name,hash,client_id,created_at,updated_at) VALUES('QA_HASH_READ','QA hash read',?,6,'2020-01-01','2020-01-02') RETURNING id",[$hash])->id;
  App\Models\Exams\Exam::withoutGlobalScopes()->select('id','name')->findOrFail($id);
  $r=$db->selectOne('SELECT hash,updated_at FROM exams WHERE id=?',[$id]);
  if($r->hash!==$hash||$r->updated_at!=='2020-01-02 00:00:00')throw new RuntimeException('Partial selection changed persisted metadata');
  App\Models\Exams\Exam::withoutGlobalScopes()->findOrFail($id);
  $r=$db->selectOne('SELECT hash,updated_at FROM exams WHERE id=?',[$id]);
  if($r->updated_at!=='2020-01-02 00:00:00'||($hash!==null&&$r->hash!==$hash)||empty($r->hash))throw new RuntimeException('Full selection changed historical timestamp or stored hash');
 }
 $db->rollBack();echo "PASS partial/full reads preserve stored hashes and historical timestamps; empty hash fill remains supported\n";
}catch(Throwable $e){$db->rollBack();throw $e;}

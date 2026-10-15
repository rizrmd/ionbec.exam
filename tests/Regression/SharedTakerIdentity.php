<?php
require __DIR__.'/../../vendor/autoload.php'; $app=require __DIR__.'/../../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
function check($v,$m){if(!$v)throw new RuntimeException($m);echo "PASS $m\n";}
if (!app()->environment('testing')) { throw new RuntimeException('Run only against isolated testing database.'); }
DB::beginTransaction();
try {
 $ids=[];$logins=[];
 foreach(['A','B'] as $label) {
  $r=DB::selectOne("INSERT INTO takers(name,reg,email,password,is_verified,client_id) VALUES(?,?,?,?,true,6) RETURNING id,login_id",['QA IDENTITY '.$label,'QA-'.$label,'identity-test@example.invalid',bcrypt('Staging-only-passphrase')]);$ids[]=$r->id;$logins[]=$r->login_id;
 }
 $session=$app['session']->driver();$session->start();$session->put('client_id',6);
 $app->instance('current_client',(object)['id'=>6]);
 $app->make(Illuminate\Contracts\Http\Kernel::class); // register route collection
 function attemptLogin($data,$session,$app) {
  Auth::guard('taker')->logout();$request=Request::create('/taker-login','POST',$data,[],[],['HTTP_REFERER'=>'http://localhost/taker-login']);$request->setLaravelSession($session);$app->instance('request',$request);
  return $app->make(App\Http\Controllers\Taker\AuthController::class)->signIn($request);
 }
 $base=['email'=>'identity-test@example.invalid','password'=>'Staging-only-passphrase'];
 attemptLogin($base,$session,$app);check(Auth::guard('taker')->guest(),'ambiguous email rejected');
 foreach($ids as $n=>$id) {attemptLogin($base+['login_id'=>$logins[$n]],$session,$app);check((int)Auth::guard('taker')->id()===(int)$id,'explicit identity '.$n.' authenticates correct account');}
 attemptLogin(['email'=>$base['email'],'password'=>'wrong','login_id'=>$logins[0]],$session,$app);check(Auth::guard('taker')->guest(),'wrong password rejected');
 $app->instance('current_client',(object)['id'=>3]);attemptLogin($base+['login_id'=>$logins[0]],$session,$app);check(Auth::guard('taker')->guest(),'cross tenant identity rejected');
 $app->instance('current_client',(object)['id'=>6]);
 $single=DB::selectOne("INSERT INTO takers(name,email,password,is_verified,client_id) VALUES(?,?,?,true,6) RETURNING id",['QA SINGLE','identity-single@example.invalid',bcrypt('Staging-only-passphrase')]);
 attemptLogin(['email'=>'identity-single@example.invalid','password'=>'Staging-only-passphrase'],$session,$app);check((int)Auth::guard('taker')->id()===(int)$single->id,'unique email legacy login preserved');Auth::guard('taker')->logout();
 DB::statement('SAVEPOINT duplicate_identity');$rejected=false;
 try {DB::insert('INSERT INTO takers(name,email,login_id,client_id) VALUES(?,?,?,6)',['Duplicate','another@example.invalid',$logins[0]]);}catch(Throwable $e){$rejected=str_contains($e->getMessage(),'23505');DB::statement('ROLLBACK TO SAVEPOINT duplicate_identity');}
 check($rejected,'database rejects duplicate participant login ID');
 DB::rollBack();echo "All identity fixtures rolled back; no scoring/notification dispatched.\n";
}catch(Throwable $e){DB::rollBack();fwrite(STDERR,get_class($e).': '.$e->getMessage()."\n".$e->getTraceAsString());exit(1);}

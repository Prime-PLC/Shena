<?php
if (PHP_SAPI !== 'cli') exit('CLI only');
class BaseController { protected function requireAuth() {} }
class ClaimEvidenceService { public function find($type,$id) { return ['member_id'=>23]; } }
class Member { public function findByUserId($id) { return ['id'=>23]; } }
require_once __DIR__.'/../app/controllers/ClaimEvidenceController.php';
$method=new ReflectionMethod(ClaimEvidenceController::class,'authorize');
$_SESSION=['user_id'=>1,'user_role'=>'member'];
$result=$method->invoke(new ClaimEvidenceController(),'funeral',1);
if ($result[1]!==false) throw new Exception('Member granted admin actions');
$_SESSION['user_role']='manager';
$result=$method->invoke(new ClaimEvidenceController(),'inpatient',1);
if ($result[1]!==true) throw new Exception('Manager denied');
// Check fail-closed ownership in an isolated process, without a web server or database.
$source=file_get_contents(__FILE__);
$source=str_replace("return ['id'=>23];", "return ['id'=>99];", $source);
$source=substr($source,0,strpos($source,'// Check fail-closed'));
$source=str_replace("__DIR__.'/../app/controllers/ClaimEvidenceController.php'",var_export(__DIR__.'/../app/controllers/ClaimEvidenceController.php',true),$source);
$tmp=tempnam(sys_get_temp_dir(),'claim-access-');file_put_contents($tmp,$source);
exec(escapeshellarg(PHP_BINARY).' -n '.escapeshellarg($tmp),$out,$code);unlink($tmp);
if (implode('', $out)!=='Access denied.') throw new Exception('Another member accessed claim');
echo "Claim evidence access tests passed\n";

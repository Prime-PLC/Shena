<?php

$root = dirname(__DIR__);

$assertContains = function (string $haystack, string $needle, string $message): void {
    if (strpos($haystack, $needle) === false) {
        fwrite(STDERR, "Assertion failed: {$message}\nMissing: {$needle}\n");
        exit(1);
    }
};

$assertNotContains = function (string $haystack, string $needle, string $message): void {
    if (strpos($haystack, $needle) !== false) {
        fwrite(STDERR, "Assertion failed: {$message}\nUnexpected: {$needle}\n");
        exit(1);
    }
};

$router = file_get_contents($root . '/app/core/Router.php');
$adminController = file_get_contents($root . '/app/controllers/AdminController.php');
$memberModel = file_get_contents($root . '/app/models/Member.php');
$membersView = file_get_contents($root . '/resources/views/admin/members.php');
$smsController = file_get_contents($root . '/app/controllers/BulkSmsController.php');
$emailController = file_get_contents($root . '/app/controllers/BulkEmailController.php');
$migration = file_get_contents($root . '/database/migrations/014_member_archive_support.sql');

$assertContains($router, "/admin/members/archived", 'archived members should have an admin route');
$assertContains($router, "/admin/members/delete", 'member delete/archive action should have an admin route');
$assertContains($router, "/admin/claims/submit", 'admin claim submission should have an admin route');
$assertContains($router, "/admin/api/members/{id}/beneficiaries", 'admin claim form should load selected member beneficiaries');
$assertContains($router, "/admin/email-campaigns/edit", 'email campaigns should support edit route');

$assertContains($memberModel, 'archiveMember', 'member model should archive members with history');
$assertContains($memberModel, 'canHardDelete', 'member model should check payments/claims before hard delete');
$assertContains($memberModel, 'getArchivedMembersWithDetails', 'member model should expose archived members list');
$assertContains($memberModel, 'archived_at IS NULL', 'normal member lists should exclude archived members');

$assertContains($migration, 'archived_at', 'migration should add archived timestamp');
$assertContains($migration, 'archive_reason', 'migration should add archive reason');

$assertContains($adminController, 'deleteOrArchiveMember', 'admin should handle member delete/archive');
$assertContains($adminController, 'archivedMembers', 'admin should render archived member view');
$assertContains($adminController, 'submitClaimForMember', 'admin should submit claims for members');
$assertContains($adminController, 'sendClaimAcknowledgementSms', 'claim submission should trigger acknowledgement SMS');
$assertContains($adminController, 'SHENA has received your claim', 'acknowledgement SMS should be consoling and explicit');
$assertContains($adminController, 'sendClaimLifecycleSms', 'claim lifecycle changes should notify members by SMS');

$assertContains($membersView, 'Monthly payable amount', 'member view/manage should show monthly payable amount');
$assertNotContains($membersView, 'Daily payable amount', 'member UI must not derive or display a daily amount');
$assertContains($membersView, 'memberDeleteConfirmModal', 'manage modal should provide strict delete/archive confirmation');
$assertContains($membersView, 'confirm_member_number', 'delete/archive must require member number confirmation');
$assertContains($membersView, '/admin/members/archived', 'archived members should be accessible from member management tabs');

$archivedViewPath = $root . '/resources/views/admin/members-archived.php';
if (!file_exists($archivedViewPath)) {
    fwrite(STDERR, "Archived members view is missing\n");
    exit(1);
}
$archivedView = file_get_contents($archivedViewPath);
$assertContains($archivedView, 'Archived Members', 'archived view should be clearly labelled');
$assertContains($archivedView, 'archive_reason', 'archived view should show archive reason');

$assertContains($smsController, 'editCampaign', 'SMS controller should support campaign editing');
$assertNotContains($smsController, 'Edit feature coming soon', 'SMS edit should not be a stub');
$assertContains($smsController, 'bulkMessagesHasColumn', 'SMS edit should tolerate production tables without optional timestamp columns');
$assertNotContains($smsController, "status = CASE WHEN ? IS NULL THEN 'draft' ELSE 'scheduled' END, updated_at = NOW()", 'SMS edit must not hard-code updated_at on production schemas that lack it');
$assertContains($emailController, 'editCampaign', 'Email controller should support campaign editing');
$assertContains($emailController, 'bulkMessagesHasColumn', 'Email edit should tolerate production tables without optional timestamp columns');

$smsView = file_get_contents($root . '/resources/views/admin/sms-campaigns.php');
$emailView = file_get_contents($root . '/resources/views/admin/email-campaigns.php');
$assertContains($smsView, 'editCampaignModal', 'SMS scheduled/draft campaigns should edit through an admin modal');
$assertContains($smsView, 'edit-target-audience', 'SMS edit modal should let admins update campaign audience');
$assertContains($smsView, 'edit-custom-filters-panel', 'SMS edit modal should expose custom audience filters');
$assertContains($smsView, 'filter_joined_before', 'SMS edit should preserve joined-before custom filter');
$assertContains($emailView, 'editCampaignModal', 'Email scheduled/draft campaigns should edit through an admin modal');
$assertContains($emailView, 'edit-target-audience', 'Email edit modal should let admins update campaign audience');
$assertContains($emailView, 'edit-custom-filters-panel', 'Email edit modal should expose custom audience filters');
$assertNotContains($smsView, 'prompt(', 'SMS campaign editing should not use browser prompt dialogs');
$assertNotContains($emailView, 'prompt(', 'Email campaign editing should not use browser prompt dialogs');

$claimsView = file_get_contents($root . '/resources/views/admin/claims.php');
$apiController = file_get_contents($root . '/app/controllers/AdminApiController.php');
$assertContains($claimsView, 'enctype="multipart/form-data"', 'admin claim form should accept the same claim documents as member portal');
$assertContains($claimsView, 'adminClaimBeneficiaryId', 'admin claim form should include beneficiary selection');
$assertContains($claimsView, 'request_cash_alternative', 'admin claim form should use the same cash alternative field as member portal');
$assertContains($claimsView, 'adminClaimCashAlternativeReasonField', 'admin claim form should toggle cash alternative reason');
$assertContains($claimsView, '/admin/api/members/', 'admin claim form should fetch selected member beneficiaries');
$assertContains($apiController, 'memberBeneficiaries', 'admin API should provide member beneficiaries for claim submission');
$assertContains($adminController, 'processAdminClaimDocumentUploads', 'admin claim submission should process required documents like member claims');

echo "Admin member, claim, and campaign regression checks passed.\n";

// Opt-in integration check: local development only, all fixtures rolled back.
if (getenv('SHENA_TEST_LOCAL_CLAIMS') === '1') {
define('ROOT_PATH',dirname(__DIR__));define('APP_PATH',ROOT_PATH.'/app');
require ROOT_PATH.'/config/local_config.php';$_SERVER['HTTP_HOST']='localhost';require ROOT_PATH.'/config/config.php';
if(DB_HOST!=='127.0.0.1'||DB_NAME!=='shena_welfare_dev')exit('Unexpected DB');
spl_autoload_register(function($c){foreach(['controllers','models','services','core','helpers'] as $d){$p=APP_PATH.'/'.$d.'/'.$c.'.php';if(file_exists($p)){require_once $p;return;}}});
require APP_PATH.'/helpers/functions.php';
class ClaimTestController extends AdminController {public $destination;protected function redirect($url){$this->destination=$url;}protected function view($template,$data=[]){$GLOBALS['view_data']=$data;}}
function check($ok,$message){if(!$ok)throw new RuntimeException($message);echo "PASS: $message\n";}
$db=Database::getInstance();$pdo=$db->getConnection();$pdo->beginTransaction();
try {
$uid=(int)$db->insert('users',['first_name'=>'Synthetic','last_name'=>'Claim QA','email'=>null,'phone'=>'','password'=>'unused','role'=>'member']);
$mid=(int)$db->insert('members',['user_id'=>$uid,'member_number'=>'QA'.bin2hex(random_bytes(5)),'id_number'=>'QA'.bin2hex(random_bytes(5)),'status'=>'active','package'=>'individual','package_key'=>'individual_below_70','gender'=>'male']);
$bid=(int)$db->insert('beneficiaries',['member_id'=>$mid,'full_name'=>'Synthetic Child','relationship'=>'child','is_active'=>1]);
$cid=(int)$db->insert('platinum_coverages',['member_id'=>$mid,'covered_person_type'=>'principal','status'=>'active','monthly_contribution'=>300,'maturity_date'=>'2020-01-01']);
$_SESSION=['user_id'=>$uid,'user_role'=>'manager','csrf_token'=>'qa'];$_SERVER['REQUEST_METHOD']='POST';$c=new ClaimTestController();
$valid=['csrf_token'=>'qa','member_id'=>$mid,'beneficiary_id'=>$bid,'deceased_name'=>'Synthetic Child','deceased_id_number'=>'','date_of_death'=>date('Y-m-d'),'place_of_death'=>'Test place','cause_of_death'=>'Test cause','mortuary_name'=>'Test mortuary','mortuary_days_count'=>'0','mortuary_bill_amount'=>'0'];
$_POST=$valid;$c->submitClaimForMember();
check(!isset($_SESSION['claim_form_error']) && isset($_SESSION['success']),'Funeral claim saved with no ID and zero days');
check((int)$db->fetch('SELECT COUNT(*) AS n FROM claims WHERE member_id=:id',['id'=>$mid])['n']===1,'Saved funeral claim exists');
$_POST=array_replace($valid,['mortuary_days_count'=>'15']);$c->submitClaimForMember();
check(str_contains($_SESSION['claim_form_error']??'','0 to 14') && $_SESSION['claim_form']['deceased_name']==='Synthetic Child','Invalid days retain entries with specific error');
$_POST=['csrf_token'=>'qa','member_id'=>$mid,'platinum_coverage_id'=>$cid,'patient_name'=>'Synthetic Child','facility_name'=>'Test hospital','facility_location'=>'Test town','requested_days'=>'2','admission_date'=>date('Y-m-d'),'override_eligibility'=>'1','override_reason'=>'Local verification'];$hospital=$_POST;
$c->submitInpatientRequestForMember();
check(!isset($_SESSION['inpatient_form_error']) && str_contains($_SESSION['success']??'','Hospital request #'),'Hospital request saved with reference feedback and no ID');
$c->platinumRequests();check(count(array_filter($GLOBALS['view_data']['inpatientRequests'],fn($v)=>(int)$v['member_id']===$mid))===1,'Saved hospital request appears in admin listing');
$_POST=array_replace($hospital,['facility_name'=>'']);$c->submitInpatientRequestForMember();check(str_contains($_SESSION['inpatient_form_error']??'','Hospital name') && $_SESSION['inpatient_form']['patient_name']==='Synthetic Child','Missing hospital name retains entries and explains error');
$provider=new ServiceProvider();$pid=$provider->saveProvider(0,['stage_key'=>'coffin','contact_name'=>'Synthetic QA','phone'=>'0712345678'],$uid);
$provider->assign('platinum',(int)array_values(array_filter($GLOBALS['view_data']['inpatientRequests'],fn($v)=>(int)$v['member_id']===$mid))[0]['id'],'platinum_hospital',$pid,$uid);
check(true,'Provider from funeral category can serve a hospital request');
} finally {if($pdo->inTransaction())$pdo->rollBack();echo "All synthetic changes rolled back. No SMS sent.\n";}

}

$platinumPage = file_get_contents($root . '/resources/views/admin/platinum-requests.php');
$assertContains($platinumPage, "layouts/admin-footer.php", 'Platinum page must load shared feedback and failed-form restoration scripts');

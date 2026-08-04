<?php

$root = dirname(__DIR__);
$failures = [];

$read = static fn(string $path): string => file_get_contents($root . '/' . $path);
$assertContains = static function (string $content, string $needle, string $message) use (&$failures): void {
    if (strpos($content, $needle) === false) {
        $failures[] = $message;
    }
};
$assertNotContains = static function (string $content, string $needle, string $message) use (&$failures): void {
    if (strpos($content, $needle) !== false) {
        $failures[] = $message;
    }
};

$memberModel = $read('app/models/Member.php');
$adminController = $read('app/controllers/AdminController.php');
$memberController = $read('app/controllers/MemberController.php');
$adminClaims = $read('resources/views/admin/claims.php');
$memberClaims = $read('resources/views/member/claims.php');
$memberBeneficiaries = $read('resources/views/member/beneficiaries.php');
$agentMember = $read('resources/views/agent/member-details.php');
$adminMember = $read('resources/views/admin/member-details.php');
$migration = $read('database/migrations/017_optional_dependant_id_and_member_file_number.sql');

$assertNotContains($memberModel, '|| $idNumber === \'\'', 'Central dependant policy must not require an ID number.');
$assertContains($migration, 'MODIFY id_number VARCHAR(20) NULL', 'Migration must permit dependant records without an ID number.');
$assertContains($memberBeneficiaries, 'ID Number (Optional)', 'Member dependant forms must label ID as optional.');
$assertContains($agentMember, 'ID Number (Optional)', 'Agent dependant form must label ID as optional.');
$assertNotContains($memberBeneficiaries, 'name="id_number" class="form-control" value="<?php echo getOldValue(\'id_number\'); ?>" required', 'Member dependant add form must not require ID in HTML.');

$assertContains($adminMember, 'name="file_number"', 'Admin member panel must expose file number.');
$assertContains($adminController, "['file_number']", 'Admin update must persist file number.');
$assertContains($memberModel, 'm.file_number LIKE :search_file_number', 'Member search must support file number.');

foreach ([$adminClaims, $memberClaims] as $claimsView) {
    $assertContains($claimsView, 'Can be added later', 'Claim form must explain that supporting documents can be added later.');
    foreach (['id_copy', 'chief_letter', 'mortuary_invoice'] as $field) {
        $assertNotContains($claimsView, 'name="' . $field . '" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required', 'Claim document fields must not be browser-required.');
    }
}
$assertNotContains($adminController, "'id_copy' => ['required' => true", 'Admin filing must not classify claim uploads as mandatory.');
$assertNotContains($memberController, "'id_copy' => ['required' => true", 'Member filing must not classify claim uploads as mandatory.');

if ($failures !== []) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

echo "Member file number, optional dependant ID, and flexible claim documents regression checks passed.\n";

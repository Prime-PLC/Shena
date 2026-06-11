<?php

$root = dirname(__DIR__);
$failed = false;

$assertContains = static function (string $contents, string $needle, string $message) use (&$failed): void {
    if (strpos($contents, $needle) === false) {
        fwrite(STDERR, $message . "\nMissing: {$needle}\n");
        $failed = true;
    }
};

$memberModel = file_get_contents($root . '/app/models/Member.php');
$membersView = file_get_contents($root . '/resources/views/admin/members.php');
$memberDetailsView = file_get_contents($root . '/resources/views/admin/member-details.php');
$detailsMethodStart = strpos($memberModel, 'public function getAllMembersWithDetails');
$detailsMethodEnd = strpos($memberModel, 'public function getArchivedMembersWithDetails', $detailsMethodStart);
$memberDetailsQuery = ($detailsMethodStart !== false && $detailsMethodEnd !== false)
    ? substr($memberModel, $detailsMethodStart, $detailsMethodEnd - $detailsMethodStart)
    : '';

$assertContains(
    $memberDetailsQuery,
    'latest_payment.amount as last_payment_amount',
    'Member management must select the latest payment amount for the Last Contribution column.'
);
$assertContains(
    $memberDetailsQuery,
    'COALESCE(latest_payment.payment_date, latest_payment.created_at) as last_payment_date',
    'Member management must select the latest available payment timestamp for the Last Contribution column.'
);
$assertContains(
    $memberDetailsQuery,
    "lp.status = 'completed'",
    'Member management last contribution should use completed payments only.'
);
$assertContains(
    $memberDetailsQuery,
    'ORDER BY COALESCE(lp.payment_date, lp.created_at) DESC, lp.id DESC',
    'Member management last contribution should pick the most recent completed payment deterministically.'
);
$assertContains(
    $membersView,
    '$hasLastPayment = !empty($member[\'last_payment_date\']);',
    'Member management view should explicitly detect whether a last payment exists.'
);
$assertContains(
    $membersView,
    'No completed payment',
    'Member management view should not show 01 Jan 1970 when no completed payment exists.'
);

$assertContains(
    $memberDetailsView,
    "String(relationship.value || '').trim().toLowerCase()",
    'Dependant DOB constraints must normalize relationship values before deciding child/adult rules.'
);
$assertContains(
    $memberDetailsView,
    "if (relation === 'child')",
    'Child dependant selection should use normalized relationship value.'
);
$assertContains(
    $memberDetailsView,
    "updateDependantDobConstraints('add');",
    'Opening the add dependant panel should refresh DOB constraints for the current relationship.'
);
$assertContains(
    $memberDetailsView,
    'dob.removeAttribute(\'max\')',
    'Child dependant DOB should clear stale adult max before applying child limits.'
);

exit($failed ? 1 : 0);

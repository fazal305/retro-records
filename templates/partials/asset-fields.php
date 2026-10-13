<?php
use App\Repositories\AssetRepository;
use App\View;

$prefix ??= '';
$options = static fn (array $values) => array_combine($values, array_map('ucfirst', $values));
?>
<?= View::partial('field', [
    'name' => 'hostname', 'id' => $prefix . 'hostname', 'label' => 'Hostname', 'value' => $asset['hostname'] ?? '',
    'hint' => 'e.g. web-01.example.net', 'error' => $errors['hostname'] ?? null,
    'attrs' => ['required' => true, 'maxlength' => '253', 'autocomplete' => 'off', 'spellcheck' => 'false'],
]) ?>
<div class="r-grid r-grid-2 field-pair">
    <?= View::partial('field', [
        'name' => 'ip_address', 'id' => $prefix . 'ip_address', 'label' => 'IP address (optional)', 'value' => $asset['ip_address'] ?? '',
        'hint' => 'IPv4 or IPv6', 'error' => $errors['ip_address'] ?? null,
        'attrs' => ['maxlength' => '45', 'autocomplete' => 'off', 'spellcheck' => 'false', 'inputmode' => 'text'],
    ]) ?>
    <?= View::partial('field', [
        'name' => 'location', 'id' => $prefix . 'location', 'label' => 'Location (optional)', 'value' => $asset['location'] ?? '',
        'hint' => 'Data centre, rack or cloud region', 'error' => $errors['location'] ?? null,
        'attrs' => ['maxlength' => '120'],
    ]) ?>
    <?= View::partial('field', [
        'name' => 'environment', 'id' => $prefix . 'environment', 'label' => 'Environment', 'type' => 'select',
        'options' => $options(AssetRepository::ENVIRONMENTS), 'value' => $asset['environment'] ?? 'production',
        'error' => $errors['environment'] ?? null, 'attrs' => ['required' => true],
    ]) ?>
    <?= View::partial('field', [
        'name' => 'status', 'id' => $prefix . 'status', 'label' => 'Status', 'type' => 'select',
        'options' => $options(AssetRepository::STATUSES), 'value' => $asset['status'] ?? 'online',
        'error' => $errors['status'] ?? null, 'attrs' => ['required' => true],
    ]) ?>
</div>
<?= View::partial('field', [
    'name' => 'notes', 'id' => $prefix . 'notes', 'label' => 'Notes (optional)', 'type' => 'textarea', 'value' => $asset['notes'] ?? '',
    'hint' => 'Up to 2,000 characters. Don\'t paste passwords or keys here.', 'error' => $errors['notes'] ?? null,
    'attrs' => ['maxlength' => '2000'],
]) ?>

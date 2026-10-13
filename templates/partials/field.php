<?php
$type ??= 'text';
$value ??= '';
$hint ??= null;
$error ??= null;
$attrs ??= [];
$id = $id ?? 'f-' . $name;
$describedBy = trim(($hint ? "$id-hint " : '') . ($error ? "$id-error" : ''));
$attrString = '';
foreach ($attrs as $attr => $attrValue) {
    $attrString .= $attrValue === true ? ' ' . e($attr) : ' ' . e($attr) . '="' . e($attrValue) . '"';
}
$common = 'id="' . e($id) . '" name="' . e($name) . '" class="r-input-block"' . $attrString
    . ($describedBy ? ' aria-describedby="' . e($describedBy) . '"' : '')
    . ($error ? ' aria-invalid="true"' : '');
?>
<div class="r-field<?= $error ? ' has-error' : '' ?>">
    <label for="<?= e($id) ?>"><?= e($label) ?></label>
    <?php if ($type === 'textarea'): ?>
        <textarea <?= $common ?> rows="4"><?= e($value) ?></textarea>
    <?php elseif ($type === 'select'): ?>
        <select <?= $common ?>>
            <?php foreach ($options as $optionValue => $optionLabel): ?>
                <option value="<?= e($optionValue) ?>"<?= (string) $optionValue === (string) $value ? ' selected' : '' ?>><?= e($optionLabel) ?></option>
            <?php endforeach; ?>
        </select>
    <?php else: ?>
        <input type="<?= e($type) ?>" value="<?= $type === 'password' ? '' : e($value) ?>" <?= $common ?>>
    <?php endif; ?>
    <?php if ($hint): ?><span class="r-hint" id="<?= e($id) ?>-hint"><?= e($hint) ?></span><?php endif; ?>
    <?php if ($error): ?><span class="r-hint field-error" id="<?= e($id) ?>-error"><?= e($error) ?></span><?php endif; ?>
</div>

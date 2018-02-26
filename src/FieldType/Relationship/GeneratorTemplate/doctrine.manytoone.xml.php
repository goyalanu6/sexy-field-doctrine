<?php if ($type === 'bidirectional') { ?>
<many-to-one field="<?php echo $toHandle; ?>" target-entity="<?php echo $toFullyQualifiedClassName; ?>" inversed-by="<?php echo $fromPluralHandle; ?>">
    <join-column name="<?php echo $toHandle; ?>_id" referenced-column-name="id" />
</many-to-one>
<?php } ?>

<?php if ($type === 'unidirectional') { ?>
<many-to-one field="<?php echo $toHandle; ?>" target-entity="<?php echo $toFullyQualifiedClassName; ?>">
    <join-column name="<?php echo $toHandle; ?>_id" referenced-column-name="id" />
</many-to-one>
<?php } ?>

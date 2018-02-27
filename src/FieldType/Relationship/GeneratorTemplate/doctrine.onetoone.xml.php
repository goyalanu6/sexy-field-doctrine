<?php if ($type === 'bidirectional' && !$owner) { ?>
<one-to-one field="<?php echo $toHandle; ?>" target-entity="<?php echo $toFullyQualifiedClassName; ?>" mapped-by="<?php echo $fromHandle; ?>" />
<?php } ?>

<?php if ($type === 'bidirectional' && $owner) { ?>
<one-to-one field="<?php echo $toHandle; ?>" target-entity="<?php echo $toFullyQualifiedClassName; ?>" inversed-by="<?php echo $fromHandle; ?>">
    <join-column name="<?php echo $toHandle; ?>_id" referenced-column-name="id" />
</one-to-one>
<?php } ?>

<?php if ($type === 'unidirectional') { ?>
<one-to-one field="<?php echo $toHandle; ?>" target-entity="<?php echo $toFullyQualifiedClassName; ?>">
   <join-column name="<?php echo $toHandle; ?>_id" referenced-column-name="id" />
</one-to-one>
<?php } ?>

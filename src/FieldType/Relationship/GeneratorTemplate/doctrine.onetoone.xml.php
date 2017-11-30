<?php if ($type === 'inverseSide') { ?>
<one-to-one field="<?php echo $toHandle; ?>" target-entity="<?php echo $toFullyQualifiedClassName; ?>" mapped-by="<?php echo $fromHandle; ?>" />
<?php } ?>

<?php if ($type === 'owningSide') { ?>
<one-to-one field="<?php echo $toHandle; ?>" target-entity="<?php echo $toFullyQualifiedClassName; ?>" inversed-by="<?php echo $fromHandle; ?>">
  <join-column name="<?php echo $toHandle; ?>_id" referenced-column-name="id" />
</one-to-one>
<?php } ?>

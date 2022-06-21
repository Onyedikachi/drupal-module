<?php
  namespace Drupal\companies\Entity;

  use Drupal\Core\Entity\ContentEntityBase;
  use Drupal\Core\Field\BaseFieldDefinition;
  use Drupal\Core\Entity\EntityTypeInterface;
  use Drupal\Core\Entity\ContentEntityInterface;

    /**
   * Defines the Company entity.
   *
   * @ingroup companies
   *
   * @ContentEntityType(
   *   id = "companies",
   *   label = @Translation("Company"),
   *   base_table = "companies",
   *   entity_keys = {
   *     "id" = "id",
   *     "uuid" = "uuid",
   *   },
   * )
   */

  class Company extends ContentEntityBase implements ContentEntityInterface {

    public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
      // $fields = parent::baseFieldDefinitions($entity_type);
      // Standard field, used as unique if primary index.
      $fields['id'] = BaseFieldDefinition::create('integer')
        ->setLabel(t('ID'))
        ->setDescription(t('The ID row of the Company entity.'))
        ->setReadOnly(TRUE);

      // Standard field, unique outside of the scope of the current project.
      $fields['uuid'] = BaseFieldDefinition::create('uuid')
        ->setLabel(t('UUID'))
        ->setDescription(t('The UUID of the Company entity.'))
        ->setReadOnly(TRUE);

      $fields['email'] = BaseFieldDefinition::create('email')
      ->setLabel(t('Email'))
      ->setDescription(t('The email of this company.'))
      ->setDefaultValue('');

      $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('CompanyName'))
      ->setDescription(t('The name of this Company.'))
      ->setRequired(TRUE);

      $fields['phone'] = BaseFieldDefinition::create('string')
      ->setLabel(t('CompanyPhone'))
      ->setDescription(t('The Company Phone of this Company.'));

      $fields['reseller_id'] = BaseFieldDefinition::create('integer')
        ->setLabel(t('ResellerID'))
        ->setDescription(t('The Reseller ID of the Company entity.'));

      $fields['reseller'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('reseller'))
      ->setDescription(t('Determines if the Company entity is a reseller.'));

      $fields['api'] = BaseFieldDefinition::create('string')
      ->setLabel(t('CompanyApi'))
      ->setDescription(t('The api endpoint of this Company.'));

      $fields['icon'] = BaseFieldDefinition::create('string')
      ->setLabel(t('CompanyIcon'))
      ->setDescription(t('The icon url of this Company.'));

      $fields['secret'] = BaseFieldDefinition::create('string')
      ->setLabel(t('CompanySecret'))
      ->setDescription(t('The secret of this Company.'));

      $fields['template'] = BaseFieldDefinition::create('string')
      ->setLabel(t('CompanyTemplate'))
      ->setDescription(t('The template of this Company.'));

      $fields['cid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('CompanyID'))
      ->setDescription(t('This the reference company ID (cid) of the Company Entity'));

      $fields['max_user_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('MaximumUserCount'))
      ->setDescription(t('The maximum number of users that can be registered to the company'));

      $fields['max_form_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('MaximumFormCount'))
      ->setDescription(t('The maximum number of forms that can be created in the company'));

      $fields['payment_category'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('PaymentCategory'))
      ->setDescription(t('The payment category of the company'));

      $fields['max_reply_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('MaximumReplyCount'))
      ->setDescription(t('The maximum reply count for Email Forms for the company'));

      $fields['max_apireply_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('MaximumApiReplyCount'))
      ->setDescription(t('The maximum reply count for API Forms for the company'));

      $fields['package_updated'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('PackageUpdated'))
      ->setDescription(t('The time that the company package was updated.'))
      ->setDefaultValue(0);

      $fields['package_self_service'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('PackageSelfService'))
      ->setDescription(t('Determines if the package has self service feature'));

      $fields['payment_options'] = BaseFieldDefinition::create('string')
      ->setLabel(t('PaymentOption'))
      ->setDescription(t('The payment method of this Company.'));

      $fields['payment_price'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('PaymentPrice'))
      ->setDescription(t('The price paid for the Package by the Company'));

      $fields['voucher_used'] = BaseFieldDefinition::create('string')
      ->setLabel(t('VoucherUsed'))
      ->setDescription(t('The price paid for the Package by the Company'));

      return $fields;
  }
}

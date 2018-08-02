<?php

declare(strict_types=1);

namespace Tardigrades\SectionField\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;
use PHPUnit\Framework\TestCase;
use Mockery;
use Tardigrades\SectionField\QueryComponents\QueryStructure;
use Tardigrades\SectionField\ValueObject\SectionConfig;

/**
 * @coversDefaultClass Tardigrades\SectionField\Service\QuerySectionReader
 * @covers ::<private>
 */
final class QuerySectionReaderTest extends TestCase
{
    /** @var QuerySectionReader */
    private $querySectionReader;

    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function setUp(): void
    {
        /** @var EntityManager|Mockery\Mock $entityManager */
        $entityManager = Mockery::mock(EntityManager::class);
        $queryStructure = new QueryStructure();
        $queryBuilder = new QueryBuilder($entityManager);
        $expressionBuilder = new Expr();
        $entityManager->shouldReceive('getExpressionBuilder')->once()->andReturn($expressionBuilder);

        $this->querySectionReader = new QuerySectionReader($entityManager, $queryBuilder, $queryStructure);
    }

//    /**
//     * @test
//     */
//    public function it_should_add_from_to_query(): void
//    {
//        $readOptions = ReadOptions::fromArray([
//            ReadOptions::SECTION => 'This\Is\A\Product'
//        ]);
//
//        $this->querySectionReader->read($readOptions, $this->givenASectionConfig());
//        $this->assertEquals(
//            'SELECT Product FROM This\Is\A\Product Product',
//            $this->querySectionReader->getDQL()
//        );
//    }
//
//    /**
//     * @test
//     */
//    public function it_should_add_where_slug_by_fetch_fields_to_query(): void
//    {
//        $readOptions = ReadOptions::fromArray([
//            ReadOptions::SECTION => 'This\Is\A\Product',
//            ReadOptions::FETCH_FIELDS => 'slug'
//        ]);
//
//        $this->querySectionReader->read($readOptions, $this->givenASectionConfig());
//        $this->assertEquals(
//            'SELECT Product.productSlug FROM This\Is\A\Product Product',
//            $this->querySectionReader->getDQL()
//        );
//    }
//
//    /**
//     * @test
//     */
//    public function it_should_add_join_one_to_many_to_query(): void
//    {
//        $readOptions = ReadOptions::fromArray([
//            ReadOptions::SECTION => 'This\Is\A\Product',
//            ReadOptions::FETCH_FIELDS => 'slug,prices,price,currency'
//        ]);
//
//        $this->querySectionReader->read($readOptions, $this->givenASectionConfig());
//        $this->assertEquals(
//            'SELECT Product.productSlug, Price.priceSlug, Price.price, Price.currency FROM This\Is\A\Product Product LEFT JOIN This\Is\A\Price Price',
//            $this->querySectionReader->getDQL()
//        );
//    }
//
//    /**
//     * @test
//     */
//    public function it_should_add_join_many_to_many_to_query(): void
//    {
//        $readOptions = ReadOptions::fromArray([
//            ReadOptions::SECTION => 'This\Is\A\Product',
//            ReadOptions::FETCH_FIELDS => 'slug,name,categories'
//        ]);
//
//        $this->querySectionReader->read($readOptions, $this->givenASectionConfig());
//        $this->assertEquals(
//            'SELECT FROM This\Is\A\Product Product',
//            $this->querySectionReader->getDQL()
//        );
//    }
//
//    /**
//     * @test
//     */
//    public function it_should_add_where_id_to_main_section(): void
//    {
//        $readOptions = ReadOptions::fromArray([
//            ReadOptions::SECTION => 'This\Is\A\Product',
//            ReadOptions::ID => 10
//        ]);
//
//        $this->querySectionReader->read($readOptions, $this->givenASectionConfig());
//
//        $this->assertEquals(
//            'SELECT Product FROM This\Is\A\Product Product WHERE Product.id = :id',
//            $this->querySectionReader->getDQL()
//        );
//    }
//
//    /**
//     * @test
//     */
//    public function it_should_add_where_slug_to_main_section(): void
//    {
//        $readOptions = ReadOptions::fromArray([
//            ReadOptions::SECTION => 'This\Is\A\Product',
//            ReadOptions::SLUG => 'product-slug'
//        ]);
//
//        $this->querySectionReader->read($readOptions, $this->givenASectionConfig());
//
//        $this->assertEquals(
//            'SELECT Product FROM This\Is\A\Product Product WHERE Product.slug = :slug',
//            $this->querySectionReader->getDQL()
//        );
//    }
//
//    /**
//     * @test
//     */
//    public function it_should_add_where_by_field_value(): void
//    {
//        $readOptions = ReadOptions::fromArray([
//            ReadOptions::SECTION => 'This\Is\A\Product',
//            ReadOptions::FIELD => [ 'name' => 'Space Frikandel' ]
//        ]);
//
//        $this->querySectionReader->read($readOptions, $this->givenASectionConfig());
//
//        $this->assertEquals(
//            'SELECT Product FROM This\Is\A\Product Product WHERE Product.name = :field',
//            $this->querySectionReader->getDQL()
//        );
//    }
//
//    /**
//     * @test
//     */
//    public function it_should_add_where_by_multiple_field_values(): void
//    {
//        $readOptions = ReadOptions::fromArray([
//            ReadOptions::SECTION => 'This\Is\A\Product',
//            ReadOptions::FIELD => ['name' => ['Space Frikandel', 'Ongewokkel']]
//        ]);
//
//        $this->querySectionReader->read($readOptions, $this->givenASectionConfig());
//
//        $this->assertEquals(
//            'SELECT Product FROM This\Is\A\Product Product WHERE Product.name IN(:fields)',
//            $this->querySectionReader->getDQL()
//        );
//    }

    /**
     * @test
     */
    public function it_should_add_a_select_from_one_section_with_where_on_foreign_section()
    {
        $readOptions = ReadOptions::fromArray([
            ReadOptions::SECTION => 'AppBundle\Entity\ParticipantSession',
            ReadOptions::FIELD => [ 'project:product:slug' => [
                '2a5e45ab-7912-4ff7-81f0-89ca574b6a27',
                'bab11893-045b-4c26-9cc5-586d879ae4ac',
                'fd47afed-9ee0-4f7a-9bab-9e5504b5c5aa'
            ]],
            ReadOptions::FETCH_FIELDS => 'uuid,participantSessionAppointmentDate'
        ]);

        $this->querySectionReader->read($readOptions, $this->givenASessionSectionConfig());
        $dql = $this->querySectionReader->getDQL();

        $this->assertEquals('SELECT participantSession.participantSessionAppointmentDate, participantSession.uuid, project.uuid, product.uuid FROM AppBundle\Entity\ParticipantSession participantSession LEFT JOIN AppBundle\Entity\Project project WITH project = participantSession.project LEFT JOIN AppBundle\Entity\Product product WITH product = project.product WHERE product.productSlug IN(:fields)', $dql);

        /**
         * SELECT
         *   participantSession.participantSessionAppointmentDate,
         *   participantSession.uuid,
         *   project.uuid,
         *   product.uuid
         * FROM AppBundle\Entity\ParticipantSession participantSession
         * LEFT JOIN AppBundle\Entity\Project project WITH project = participantSession.project
         * LEFT JOIN AppBundle\Entity\Product product WITH product = project.product
         * WHERE product.productSlug IN(:fields)
         */
    }

    /**
     * @test
     */
    public function it_should_join_aliassed_relationships_combined_with_the_same_none_aliassed_relationship()
    {
        $readOptions = ReadOptions::fromArray([
            ReadOptions::SECTION => 'AppBundle\Entity\ParticipantSession',
            ReadOptions::FIELD => [ 'project:product:slug' => [
                '2a5e45ab-7912-4ff7-81f0-89ca574b6a27',
                'bab11893-045b-4c26-9cc5-586d879ae4ac',
                'fd47afed-9ee0-4f7a-9bab-9e5504b5c5aa'
            ]],
            ReadOptions::FETCH_FIELDS => 'uuid,accountHasRole,account,consultant,firstName,infix,lastName'
        ]);

        $this->querySectionReader->read($readOptions, $this->givenASessionSectionConfig());
        $dql = $this->querySectionReader->getDQL();

        echo $dql;


        /**
        SELECT
        participantSession.uuid,
        consultant.uuid,
        project.uuid,
        consultantAccount.firstName,
        consultantAccount.infix,
        consultantAccount.lastName,
        consultantAccount.uuid,
        accountHasRoleAccount.firstName,
        accountHasRoleAccount.infix,
        accountHasRoleAccount.lastName,
        accountHasRoleAccount.uuid,
        projectProduct.uuid,
        accountHasRole.uuid
        FROM AppBundle\Entity\ParticipantSession participantSession
        *** LEFT JOIN AppBundle\Entity\AccountHasRole consultant WITH consultant = participantSession.consultant
        *** LEFT JOIN AppBundle\Entity\Project project WITH project = participantSession.project
        *** LEFT JOIN AppBundle\Entity\Account consultantAccount WITH consultantAccount=consultant
        *** LEFT JOIN AppBundle\Entity\Account accountHasRoleAccount WITH accountHasRoleAccount=accountHasRole
        LEFT JOIN AppBundle\Entity\Product projectProduct WITH projectProduct=project
        LEFT JOIN AppBundle\Entity\AccountHasRole accountHasRole WITH accountHasRole = participantSession.accountHasRole
        WHERE product.productSlug IN(:fields)
         *
         */
        /**
         * SELECT
              participantSession.uuid AS participantSession_uuid,
              participantSession.sessionAppointmentDate AS participantSession_participantSessionAppointmentDate,
              project.uuid AS project_uuid,
              product.uuid AS product_uuid,
              consultantAccount.uuid AS consultantAccount_uuid,
              consultantAccount.firstName AS consultantAccount_firstName,
              consultantAccount.infix AS consultantAccount_infix,
              consultantAccount.lastName AS consultantAccount_lastName,
              accountHasRoleAccount.uuid AS accountHasRoleAccount_uuid,
              accountHasRoleAccount.firstName AS accountHasRoleAccount_firstName,
              accountHasRoleAccount.infix AS accountHasRoleAccount_infix,
              accountHasRoleAccount.lastName AS accountHasRoleAccount_lastName
           FROM AppBundle\Entity\ParticipantSession participantSession
              LEFT JOIN AppBundle\Entity\Project project WITH project = participantSession.project
              LEFT JOIN AppBundle\Entity\Product product WITH product = project.product

              LEFT JOIN AppBundle\Entity\AccountHasRole consultant WITH consultant = participantSession.consultant
              LEFT JOIN AppBundle\Entity\Account consultantAccount WITH consultantAccount = consultant
              LEFT JOIN AppBundle\Entity\AccountHasRole accountHasRole WITH accountHasRole = participantSession.accountHasRole
              LEFT JOIN AppBundle\Entity\Account accountHasRoleAccount WITH accountHasRoleAccount = accountHasRole

           WHERE product.productSlug IN(
              '2a5e45ab-7912-4ff7-81f0-89ca574b6a27',
              'bab11893-045b-4c26-9cc5-586d879ae4ac',
              'fd47afed-9ee0-4f7a-9bab-9e5504b5c5aa'
           )
         */
    }

    /**
     * @test
     */
    public function it_should_do_the_woo_woo(): void
    {
        $readOptions = ReadOptions::fromArray([
            ReadOptions::SECTION => 'AppBundle\Entity\Session',
            ReadOptions::FIELD => [ 'project:product:slug' => [
                '2a5e45ab-7912-4ff7-81f0-89ca574b6a27',
                'bab11893-045b-4c26-9cc5-586d879ae4ac',
                'fd47afed-9ee0-4f7a-9bab-9e5504b5c5aa'
            ]],
            ReadOptions::FETCH_FIELDS => 'uuid,participantSessionAppointmentDate,accountRole,account,firstName,infix,lastName,consultant,project,organisation,organisationName,organisationType'
        ]);

        $this->querySectionReader->read($readOptions, $this->givenASessionSectionConfig());
        $dql = $this->querySectionReader->getDQL();

        /**
         * SELECT
                ParticipantSession.uuid AS session_uuid,
                ParticipantSession.sessionAppointmentDate AS participantSession_participantSessionAppointmentDate,
                ParticipantSession.sessionAppointmentDateTimezone AS participantSession_participantSessionAppointmentDateTimezone,
                project.uuid AS project_uuid,
                consultantAccount.uuid AS consultantAccount_uuid,
                consultantAccount.firstName AS consultantAccount_firstName,
                consultantAccount.infix AS consultantAccount_infix,
                consultantAccount.lastName AS consultantAccount_lastName,
                organisation.uuid AS organisation_uuid,
                organisation.organisationName AS organisation_organisationName,
                organisation.organisationType AS organisation_organisationType,
                accountRoleAccount.uuid AS accountRoleAccount_uuid,
                accountRoleAccount.firstName AS accountRoleAccount_firstName,
                accountRoleAccount.infix AS accountRoleAccount_infix,
                accountRoleAccount.lastName AS accountRoleAccount_lastName
            FROM AppBundle\Entity\ParticipantSession ParticipantSession
            LEFT JOIN AppBundle\Entity\Project project WITH project = Session.project
            LEFT JOIN AppBundle\Entity\AccountRole consultant WITH consultant = Session.consultant
            LEFT JOIN AppBundle\Entity\Account consultantAccount WITH consultantAccount = consultant
            LEFT JOIN AppBundle\Entity\AccountRole accountHasRole WITH accountRole = Session.accountRole
            LEFT JOIN AppBundle\Entity\Account accountHasRoleAccount WITH accountHasRoleAccount = accountRole
            LEFT JOIN AppBundle\Entity\Product product WITH product = project.product
            LEFT JOIN AppBundle\Entity\Organisation organisation WITH organisation = project.organisation
            WHERE product.productSlug IN(
                '2a5e45ab-7912-4ff7-81f0-89ca574b6a27',
                'bab11893-045b-4c26-9cc5-586d879ae4ac',
                'fd47afed-9ee0-4f7a-9bab-9e5504b5c5aa'
            )
         */

        $this->assertEquals("SELECT ParticipantSession.uuid AS participantSession_uuid, ParticipantSession.participantSessionAppointmentDate AS participantSession_participantSessionAppointmentDate, ParticipantSession.participantSessionAppointmentDateTimezone AS participantSession_participantSessionAppointmentDateTimezone, project.uuid AS project_uuid, consultantAccount.uuid AS consultantAccount_uuid, consultantAccount.firstName AS consultantAccount_firstName, consultantAccount.infix AS consultantAccount_infix, consultantAccount.lastName AS consultantAccount_lastName, organisation.uuid AS organisation_uuid, organisation.organisationName AS organisation_organisationName, organisation.organisationType AS organisation_organisationType, accountHasRoleAccount.uuid AS accountHasRoleAccount_uuid, accountHasRoleAccount.firstName AS accountHasRoleAccount_firstName, accountHasRoleAccount.infix AS accountHasRoleAccount_infix, accountHasRoleAccount.lastName AS accountHasRoleAccount_lastName FROM AppBundle\Entity\ParticipantSession ParticipantSession LEFT JOIN AppBundle\Entity\Project project WITH project = ParticipantSession.project LEFT JOIN AppBundle\Entity\AccountHasRole consultant WITH consultant = ParticipantSession.consultant LEFT JOIN AppBundle\Entity\Account consultantAccount WITH consultantAccount = consultant LEFT JOIN AppBundle\Entity\AccountHasRole accountHasRole WITH accountHasRole = ParticipantSession.accountHasRole LEFT JOIN AppBundle\Entity\Account accountHasRoleAccount WITH accountHasRoleAccount = accountHasRole LEFT JOIN AppBundle\Entity\Product product WITH product = project.product LEFT JOIN AppBundle\Entity\Organisation organisation WITH organisation = project.organisation WHERE product.productSlug IN('2a5e45ab-7912-4ff7-81f0-89ca574b6a27', 'bab11893-045b-4c26-9cc5-586d879ae4ac', 'fd47afed-9ee0-4f7a-9bab-9e5504b5c5aa')", $dql);


    }

    private function givenASessionSectionConfig(): SectionConfig
    {
        return SectionConfig::fromArray([
            'section' => [
                'name' => 'Session',
                'handle' => 'session',
                'fields' => [
                    'title' => [],
                    'sessionSlug' => []
                ],
                'default' => 'title',
                'slug' => 'sessionSlug',
                'namespace' => 'AppBundle\Entity\Session'
            ]
        ]);
    }

    private function givenASectionConfig(): SectionConfig
    {
        return SectionConfig::fromArray([
            'section' => [
                'name' => 'Product',
                'handle' => 'product',
                'fields' => [
                    'title' => [],
                    'productSlug' => []
                ],
                'default' => 'title',
                'slug' => 'productSlug',
                'namespace' => 'This\Is\A\Product'
            ]
        ]);
    }
}

namespace AppBundle\Entity;

class ParticipantSession {

    // uuid, <- session,project,product
    // sessionAppointmentDate, <- session
    // accountRole, <- session -> oneSessionToOneaccountRole (oneToOne)
    // consultant, <- session -> consultant (manyToOne)
    // project, <- session -> manySessionToOneProject (manyToOne)

    public static function getFields() {
        return [
            'consultant' => [
                'handle' => 'consultant',
                'type' => 'Relationship',
                'getter' => 'getConsultant',
                'setter' => 'setConsultant',
                'relationship' => [
                    'class' => 'AppBundle\\Entity\\AccountHasRole',
                    'plural' => false,
                    'kind' => 'many-to-one',
                ],
            ],
            'accountHasRole' => [
                'handle' => 'oneSessionToOneAccountHasRole',
                'type' => 'Relationship',
                'getter' => 'getAccountRole',
                'setter' => 'setAccountRole',
                'relationship' => [
                    'class' => 'AppBundle\\Entity\\AccountHasRole',
                    'plural' => false,
                    'kind' => 'one-to-one',
                ],
            ],
            'created' => [
                'handle' => 'created',
                'type' => 'DateTimeField',
                'getter' => 'getCreated',
                'setter' => 'setCreated',
                'relationship' => null,
            ],
            'project' => [
                'handle' => 'manyParticipantSessionToOneProject',
                'type' => 'Relationship',
                'getter' => 'getProject',
                'setter' => 'setProject',
                'relationship' => [
                    'class' => 'AppBundle\\Entity\\Project',
                    'plural' => false,
                    'kind' => 'many-to-one',
                ],
            ],
            'participantSessionAppointmentDate' => [
                'handle' => 'participantSessionAppointmentDate',
                'type' => 'DateTimeTimezone',
                'getter' => 'getParticipantSessionAppointmentDate',
                'setter' => 'setParticipantSessionAppointmentDate',
                'relationship' => null,
            ],
            'participantSessionSlug' => [
                'handle' => 'participantSessionSlug',
                'type' => 'Slug',
                'getter' => 'getParticipantSessionSlug',
                'setter' => 'setParticipantSessionSlug',
                'relationship' => null,
            ],
            'updated' => [
                'handle' => 'updated',
                'type' => 'DateTimeField',
                'getter' => 'getUpdated',
                'setter' => 'setUpdated',
                'relationship' => null,
            ],
            'uuid' => [
                'handle' => 'uuid',
                'type' => 'Uuid',
                'getter' => 'getUuid',
                'setter' => 'setUuid',
                'relationship' => null,
            ]
        ];
    }
}

namespace AppBundle\Entity;

class Product
{
    public static function getFields() {
        return [
            'created' => [
                'handle' => 'created',
                'type' => 'DateTimeField',
                'getter' => 'getCreated',
                'setter' => 'setCreated',
                'relationship' => null,
            ],
            'productSlug' => [
                'handle' => 'productSlug',
                'type' => 'Slug',
                'getter' => 'getProductSlug',
                'setter' => 'setProductSlug',
                'relationship' => null,
            ],
            'updated' => [
                'handle' => 'updated',
                'type' => 'DateTimeField',
                'getter' => 'getUpdated',
                'setter' => 'setUpdated',
                'relationship' => null,
            ],
            'uuid' => [
                'handle' => 'uuid',
                'type' => 'Uuid',
                'getter' => 'getUuid',
                'setter' => 'setUuid',
                'relationship' => null,
            ]
        ];
    }
}

namespace AppBundle\Entity;

class Project {
    // organisation, <- project -> manyProjectToOneOrganisation (manyToOne)

    public static function getFields()
    {
        return [
            'created' => [
                'handle' => 'created',
                'type' => 'DateTimeField',
                'getter' => 'getCreated',
                'setter' => 'setCreated',
                'relationship' => null,
            ],
            'organisation' => [
                'handle' => 'manyProjectToOneOrganisation',
                'type' => 'Relationship',
                'getter' => 'getOrganisation',
                'setter' => 'setOrganisation',
                'relationship' => [
                    'class' => 'AppBundle\\Entity\\Organisation',
                    'plural' => false,
                    'kind' => 'many-to-one',
                ],
            ],
            'product' => [
                'handle' => 'manyProjectToOneProduct',
                'type' => 'Relationship',
                'getter' => 'getProduct',
                'setter' => 'setProduct',
                'relationship' => [
                    'class' => 'AppBundle\\Entity\\Product',
                    'plural' => false,
                    'kind' => 'many-to-one',
                ],
            ],
            'updated' => [
                'handle' => 'updated',
                'type' => 'DateTimeField',
                'getter' => 'getUpdated',
                'setter' => 'setUpdated',
                'relationship' => null,
            ],
            'projectSlug' => [
                'handle' => 'projectSlug',
                'type' => 'Slug',
                'getter' => 'getProjectSlug',
                'setter' => 'setProjectSlug',
                'relationship' => null,
            ],
            'uuid' => [
                'handle' => 'uuid',
                'type' => 'Uuid',
                'getter' => 'getUuid',
                'setter' => 'setUuid',
                'relationship' => null,
            ]
        ];
    }
}

namespace AppBundle\Entity;

class Organisation
{
    public static function getFields()
    {
        return [
            'created' => [
                'handle' => 'created',
                'type' => 'DateTimeField',
                'getter' => 'getCreated',
                'setter' => 'setCreated',
                'relationship' => null,
            ],
            'organisationName' => [
                'handle' => 'organisationName',
                'type' => 'TextInput',
                'getter' => 'getOrganisationName',
                'setter' => 'setOrganisationName',
                'relationship' => null,
            ],
            'organisationSlug' => [
                'handle' => 'organisationSlug',
                'type' => 'Slug',
                'getter' => 'getOrganisationSlug',
                'setter' => 'setOrganisationSlug',
                'relationship' => null,
            ],
            'organisationType' => [
                'handle' => 'organisationType',
                'type' => 'Choice',
                'getter' => 'getOrganisationType',
                'setter' => 'setOrganisationType',
                'relationship' => null,
            ],
            'updated' => [
                'handle' => 'updated',
                'type' => 'DateTimeField',
                'getter' => 'getUpdated',
                'setter' => 'setUpdated',
                'relationship' => null,
            ],
            'uuid' => [
                'handle' => 'uuid',
                'type' => 'Uuid',
                'getter' => 'getUuid',
                'setter' => 'setUuid',
                'relationship' => null,
            ]
        ];
    }
}

namespace AppBundle\Entity;

class AccountHasRole {

    // account, <- accountHasRole -> manyAccountHasRoleToOneAccount (manyToOne)

    public static function getFields()
    {
        return [
            'accountRoleSlug' => [
                'handle' => 'accountHasRoleSlug',
                'type' => 'Slug',
                'getter' => 'getAccountHasRoleSlug',
                'setter' => 'setAccountHasRoleSlug',
                'relationship' => null,
            ],
            'created' => [
                'handle' => 'created',
                'type' => 'DateTimeField',
                'getter' => 'getCreated',
                'setter' => 'setCreated',
                'relationship' => null,
            ],
            'account' => [
                'handle' => 'manyAccountHasRoleToOneAccount',
                'type' => 'Relationship',
                'getter' => 'getAccount',
                'setter' => 'setAccount',
                'relationship' => [
                    'class' => 'AppBundle\\Entity\\Account',
                    'plural' => false,
                    'kind' => 'many-to-one',
                ],
            ],
            'updated' => [
                'handle' => 'updated',
                'type' => 'DateTimeField',
                'getter' => 'getUpdated',
                'setter' => 'setUpdated',
                'relationship' => null,
            ],
            'uuid' => [
                'handle' => 'uuid',
                'type' => 'Uuid',
                'getter' => 'getUuid',
                'setter' => 'setUuid',
                'relationship' => null,
            ],
        ];
    }
}

namespace AppBundle\Entity;

class Account {

    // firstName, <- account
    // infix, <- account
    // lastName, <- account

    public static function getFields()
    {
        return [
            'accountSlug' => [
                'handle' => 'accountSlug',
                'type' => 'Slug',
                'getter' => 'getAccountSlug',
                'setter' => 'setAccountSlug',
                'relationship' => null,
            ],
            'created' => [
                'handle' => 'created',
                'type' => 'DateTimeField',
                'getter' => 'getCreated',
                'setter' => 'setCreated',
                'relationship' => null,
            ],
            'firstName' => [
                'handle' => 'firstName',
                'type' => 'TextInput',
                'getter' => 'getFirstName',
                'setter' => 'setFirstName',
                'relationship' => null,
            ],
            'infix' => [
                'handle' => 'infix',
                'type' => 'TextInput',
                'getter' => 'getInfix',
                'setter' => 'setInfix',
                'relationship' => null,
            ],
            'lastName' => [
                'handle' => 'lastName',
                'type' => 'TextInput',
                'getter' => 'getLastName',
                'setter' => 'setLastName',
                'relationship' => null,
            ],
            'updated' => [
                'handle' => 'updated',
                'type' => 'DateTimeField',
                'getter' => 'getUpdated',
                'setter' => 'setUpdated',
                'relationship' => null,
            ],
            'uuid' => [
                'handle' => 'uuid',
                'type' => 'Uuid',
                'getter' => 'getUuid',
                'setter' => 'setUuid',
                'relationship' => null,
            ]
        ];
    }
}

namespace This\Is\A;

/**
 * Class Product
 *
 * Why can't I implement the CommonSectionInterface?
 *
 * @package This\Is\A
 */
class Product {

    const FIELDS = [
        'productSlug' => [
            'handle' => 'productSlug',
            'type' => 'Slug',
            'getter' => 'getProductSlug',
            'setter' => 'setProductSlug',
            'relationship' => null
        ],
        'name' => [
            'handle' => 'name',
            'type' => 'TextInput',
            'getter' => 'getName',
            'setter' => 'setName',
            'relationship' => null
        ],
        'prices' => [
            'handle' => 'oneProductToManyPrice',
            'type' => 'Relationship',
            'getter' => 'getPrices',
            'setter' => 'addPrice',
            'relationship' => [
                'class' => 'This\\Is\\A\\Price',
                'plural' => true,
                'kind' => 'one-to-many'
            ]
        ],
        'categories' => [
            'handle' => 'manyProductToManyCategory',
            'type' => 'Relationship',
            'getter' => 'getProductCategories',
            'setter' => 'addProductCategory',
            'relationship' => [
                'class' => 'This\\Is\\A\\ProductCategory',
                'plural' => true,
                'kind' => 'many-to-many'
            ],
        ],
        'status' => [
            'handle' => 'manyProductToOneStatus',
            'type' => 'Relationship',
            'getter' => 'getStatus',
            'setter' => 'setStatus',
            'relationship' => [
                'class' => 'This\\Is\\A\\Status',
                'plural' => false,
                'kind' => 'many-to-one',
            ],
        ]
    ];

    /** @var string */
    protected $productSlug;

    /** @var string */
    protected $name;

    protected $prices;

    protected $categories;
}

namespace This\Is\A;

/**
 * Class Price
 * @package This\Is\A
 */
class Price
{
    const FIELDS = [
        'priceSlug' => [
            'handle' => 'priceSlug',
            'type' => 'Slug',
            'getter' => 'getPriceSlug',
            'setter' => 'setPriceSlug',
            'relationship' => null,
        ],
        'price' => [
            'handle' => 'price',
            'type' => 'TextInput',
            'getter' => 'getPrice',
            'setter' => 'setPrice',
            'relationship' => null
        ],
        'currency' => [
            'handle' => 'currency',
            'type' => 'TextInput',
            'getter' => 'getCurrency',
            'setter' => 'setCurrency',
            'relationship' => null
        ]
    ];

    /** @var string */
    protected $priceSlug;

    /** @var string */
    protected $price;

    /** @var string */
    protected $currency;
}

namespace This\Is\A;

/**
 * Class ProductCategory
 *
 * This is many to many related to a product
 *
 * @package This\Is\A
 */
class ProductCategory
{
    const FIELDS = [
        'productCategorySlug' => [
            'handle' => 'productCategorySlug',
            'type' => 'Slug',
            'getter' => 'getProductCategorySlug',
            'setter' => 'setProductCategorySlug',
            'relationship' => null
        ],
        'name' => [
            'handle' => 'name',
            'type' => 'TextInput',
            'getter' => 'getName',
            'setter' => 'setName',
            'relationship' => null
        ],
    ];
}

namespace This\Is\A;

class Status
{
    const FIELDS = [
        'statusSlug' => [
            'handle' => 'statusSlug',
            'type' => 'Slug',
            'getter' => 'getStatusSlug',
            'setter' => 'setStatusSlug',
            'relationship' => null
        ],
        'value' => [
            'handle' => 'value',
            'type' => 'TextInput',
            'getter' => 'getValue',
            'setter' => 'setValue',
            'relationship' => null
        ]
    ];
}

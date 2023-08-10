<?php

namespace FreePBX\modules\Arimanager\Api\Gql;

use GraphQLRelay\Relay;
use GraphQL\Type\Definition\Type;
use FreePBX\modules\Api\Gql\Base;
use GraphQL\Error\UserError;

class AriManagerUser extends Base
{
	protected $module = 'arimanager';
	protected $fields = [];
	protected $arimanager;

	public function __construct($freepbx, $typeContainer, $module)
	{
		parent::__construct($freepbx, $typeContainer, $module);

		$this->arimanager = $freepbx->Arimanager;
		$this->fields 	  = $this->getMutationFields();
	}

	public function mutationCallback() 
	{
		if($this->checkAllWriteScope()) 
		{
			return fn() => [
					'addAriManagerUsers' => Relay::mutationWithClientMutationId([
						'name' 			=> 'addAriManagerUsers',
						'description' 	=> _('Add a new entry to Asterisk REST Interface Users'),
						'inputFields' 	=> $this->getMutationFields(),
						'outputFields'	=> [
							'ariManagerUser' => [
								'type' => $this->typeContainer->get('ariManagerUser')->getObject(),
								'resolve' => fn($payload) => (is_countable($payload) ? count($payload) : 0) > 1 ? $payload : null
							]
						],
						'mutateAndGetPayload' => function ($input)
						{
							$name 			 = $input['name'] ?? '';
							$password 		 = $input['password'] ?? null;
							$password_format = $input['password_format'] ?? null;
							$read_only 		 = $input['read_only'] ?? null;
							try
							{
								$id = $this->arimanager->addUser($name, $read_only, $password, $password_format);
							}
							catch (\Exception $e)
							{
								throw new UserError($e->getMessage(), $e->getCode());
							}
							$item = $this->arimanager->getUser($id);
							return !empty($item) ? $item : [];
						}
					]),
					'updateAriManagerUsers' => Relay::mutationWithClientMutationId([
						'name' 			=> 'updateAriManagerUsers',
						'description' 	=> _('Update an entry in Asterisk REST Interface Users'),
						'inputFields' 	=> $this->getMutationFields(),
						'outputFields' 	=> [
							'ariManagerUser' => [
								'type' => $this->typeContainer->get('ariManagerUser')->getObject(),
								'resolve' => fn($payload) => (is_countable($payload) ? count($payload) : 0) > 1 ? $payload : null
							]
						],
						'mutateAndGetPayload' => function ($input)
						{
							$id 			 = $input['id'] ?? '';
							$name 			 = $input['name'] ?? '';
							$password 		 = $input['password'] ?? null;
							$password_format = $input['password_format'] ?? null;
							$read_only 		 = $input['read_only'] ?? null;
							try
							{
								$this->arimanager->editUser($id, $name, $read_only, $password, $password_format);
							}
							catch (\Exception $e)
							{
								throw new UserError($e->getMessage(), $e->getCode());
							}

							$item = $this->arimanager->getUser($input['id']);
							return !empty($item) ? $item : [];
						}
					]),
					'removeAriManagerUsers' => Relay::mutationWithClientMutationId([
						'name' 		  => 'removeAriManagerUsers',
						'description' => _('Remove an entry from Asterisk REST Interface Users'),
						'inputFields' => [
							'id' => [
								'type' => Type::nonNull(Type::id())
							]
						],
						'outputFields' => [
							'deletedId' => [
								'type' => Type::nonNull(Type::id()),
								'resolve' => fn($payload) => $payload['id']
							]
						],
						'mutateAndGetPayload' => function ($input)
						{
							try
							{
								$this->arimanager->deleteUser($input['id']);
							}
							catch (\Exception $e)
							{
								throw new UserError($e->getMessage(), $e->getCode());
							}
							return ['id' => $input['id']];
						}
					])
				];
		}
	}

	public function queryCallback()
	{
		if ($this->checkAllReadScope())
		{
			return fn() => [
					'allAriManagerUsers' => [
						'type' 		  => $this->typeContainer->get('ariManagerUser')->getConnectionType(),
						'description' => _('Asterisk 12 introduces the Asterisk REST Interface (ARI), a set of RESTful API\'s for building Asterisk based applications. This module provides the ability to add and remove ARI users.'),
						'args' 		  => Relay::forwardConnectionArgs(),
						'resolve' 	  => function($root, $args)
						{
							$after = !empty($args['after']) ? Relay::fromGlobalId($args['after'])['id'] : null;
							$first = !empty($args['first']) ? $args['first'] : null;
							return Relay::connectionFromArraySlice(
								$this->arimanager->getUsers($after, $first),
								$args,
								[
									'sliceStart' => !empty($after) ? $after : 0,
									'arrayLength' => $this->arimanager->getTotalUsers(),
								]
							);
						},
					],
					'ariManagerUser' => [
						'type' 		  => $this->typeContainer->get('ariManagerUser')->getObject(),
						'description' => _('Asterisk 12 introduces the Asterisk REST Interface (ARI), a set of RESTful API\'s for building Asterisk based applications. This module provides the ability to add and remove ARI users.'),
						'args' 		  => [
							'id' => [
								'type' => Type::id(),
								'description' => _('The ID'),
							]
						],
						'resolve' => function($root, $args)
						{
							try
							{
								return $this->arimanager->getUser(Relay::fromGlobalId($args['id'])['id']);
							}
							catch (\Exception $e)
							{
								throw new UserError($e->getMessage());
							}
						}
					]
				];
		}
	}

	public function initializeTypes()
	{
		$user = $this->typeContainer->create('ariManagerUser');
		$user->setDescription('%description%');

		$user->addInterfaceCallback(fn() => [
				$this->getNodeDefinition()['nodeInterface']
			]);

		$user->setGetNodeCallback(fn($id) => $this->arimanager->getUser($id));

		$user->addFieldCallback(fn() => [
				'id' 			  => Relay::globalIdField('', fn($row) => $row['id'] ?? null),
				'arimanager_id'   => [
					'type'		  => Type::nonNull(Type::int()),
					'description' => $this->fields['id']['description'],
					'resolve' 	  => fn($row) => $row['id'] ?? null
				],
				'name' 			  => [
					'type' 		  => $this->fields['name']['type'],
					'description' => $this->fields['name']['description'],
				],
				'password' 		  => [
					'type' 		  => $this->fields['password']['type'],
					'description' => $this->fields['password']['description'],
				],
				'password_format' => [
					'type' 		  => $this->fields['password_format']['type'],
					'description' => $this->fields['password_format']['description'],
				],
				'read_only' 	  => [
					'type' 		  => $this->fields['read_only']['type'],
					'description' => $this->fields['read_only']['description'],
				],

			]);

		$user->setConnectionResolveNode(fn($edge) => $edge['node']);

		$user->setConnectionFields(fn() => [
				'totalCount' => [
					'type' 	  => Type::int(),
					'resolve' => fn($value) => $this->arimanager->getTotalUsers()
				],
				'ariManagerUsers' => [
					'type' 	  => Type::listOf($this->typeContainer->get('ariManagerUser')->getObject()),
					'resolve' => function($root, $args)
					{
						$data = array_map(fn($row) => $row['node'], $root['edges']);
						return $data;
					}
				]
			]);
	}

	private function getMutationFields()
	{
		return [
			'id' => [
				'type' => Type::nonNull(Type::id()),
				'description' => ''
			],
			'name' => [
				'type' => Type::nonNull(Type::string()),
				'description' => _('Asterisk REST Interface User Name')
			],
			'password' => [
				'type' => Type::string(),
				'description' => _('Asterisk REST Inferface Password.')
			],
			'password_format' => [
				'type' => Type::string(),
				'description' => _('For the security consious, you probably don\'t want to put plaintext passwords in the configuration file. ARI supports the use of crypt(3) for password storage.')
			],
			'read_only' => [
				'type' => Type::boolean(),
				'description' => _('Set to Yes for read-only applications.')
			],
		];
	}
}
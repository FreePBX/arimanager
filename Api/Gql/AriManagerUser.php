<?php

namespace FreePBX\modules\Arimanager\Api\Gql;

use GraphQLRelay\Relay;
use GraphQL\Type\Definition\Type;
use FreePBX\modules\Api\Gql\Base;

class AriManagerUser extends Base {
	protected $module = 'arimanager';

	public function mutationCallback() {
		if($this->checkAllWriteScope()) {
			return function() {
				return [
					'addAriManagerUsers' => Relay::mutationWithClientMutationId([
						'name' => 'addAriManagerUsers',
						'description' => 'Add a new entry to Asterisk REST Interface Users',
						'inputFields' => $this->getMutationFields(),
						'outputFields' => [
							'ariManagerUser' => [
								'type' => $this->typeContainer->get('ariManagerUser')->getObject(),
								'resolve' => function ($payload) {
									return count($payload) > 1 ? $payload : null;
								}
							]
						],
						'mutateAndGetPayload' => function ($input) {
							$sql = "INSERT INTO arimanager (`id`,`name`,`password`,`password_format`,`read_only`) VALUES (:id,:name,:password,:password_format,:read_only)";
							$sth = $this->freepbx->Database->prepare($sql);
							$sth->execute($this->getMutationExecuteArray($input));
							$item = $this->getSingleData($input['id']);
							return !empty($item) ? $item : [];
						}
					]),
					'updateAriManagerUsers' => Relay::mutationWithClientMutationId([
						'name' => 'updateAriManagerUsers',
						'description' => 'Update an entry in Asterisk REST Interface Users',
						'inputFields' => $this->getMutationFields(),
						'outputFields' => [
							'ariManagerUser' => [
								'type' => $this->typeContainer->get('ariManagerUser')->getObject(),
								'resolve' => function ($payload) {
									return count($payload) > 1 ? $payload : null;
								}
							]
						],
						'mutateAndGetPayload' => function ($input) {
							$item = $this->getSingleData($input['id']);
							if(empty($tiem)) {
								return null;
							}
							$sql = "UPDATE arimanager SET `id` = :id,`name` = :name,`password` = :password,`password_format` = :password_format,`read_only` = :read_only WHERE `id` = :id";
							$sth = $this->freepbx->Database->prepare($sql);
							$sth->execute($this->getMutationExecuteArray($input));
							$item = $this->getSingleData($input['id']);
							return !empty($item) ? $item : [];
						}
					]),
					'removeAriManagerUsers' => Relay::mutationWithClientMutationId([
						'name' => 'removeAriManagerUsers',
						'description' => 'Remove an entry from Asterisk REST Interface Users',
						'inputFields' => [
							'id' => [
								'type' => Type::nonNull(Type::id())
							]
						],
						'outputFields' => [
							'deletedId' => [
								'type' => Type::nonNull(Type::id()),
								'resolve' => function ($payload) {
									return $payload['id'];
								}
							]
						],
						'mutateAndGetPayload' => function ($input) {
							$sql = "DELETE FROM arimanager WHERE `id` = :id";
							$sth = $this->freepbx->Database->prepare($sql);
							$sth->execute([
								":id" => $input['id']
							]);
							return ['id' => $input['id']];
						}
					])
				];
			};
		}
	}

	public function queryCallback() {
		if($this->checkAllReadScope()) {
			return function() {
				return [
					'allAriManagerUsers' => [
						'type' => $this->typeContainer->get('ariManagerUser')->getConnectionType(),
						'description' => 'Asterisk 12 introduces the Asterisk REST Interface (ARI), a set of RESTful API\'s for building Asterisk based applications. This module provides the ability to add and remove ARI users.',
						'args' => Relay::forwardConnectionArgs(),
						'resolve' => function($root, $args) {
							$after = !empty($args['after']) ? Relay::fromGlobalId($args['after'])['id'] : null;
							$first = !empty($args['first']) ? $args['first'] : null;
							return Relay::connectionFromArraySlice(
								$this->getData($after,$first),
								$args,
								[
									'sliceStart' => !empty($after) ? $after : 0,
									'arrayLength' => $this->getTotal()
								]
							);
						},
					],
					'ariManagerUser' => [
						'type' => $this->typeContainer->get('ariManagerUser')->getObject(),
						'description' => 'Asterisk 12 introduces the Asterisk REST Interface (ARI), a set of RESTful API\'s for building Asterisk based applications. This module provides the ability to add and remove ARI users.',
						'args' => [
							'id' => [
								'type' => Type::id(),
								'description' => 'The ID',
							]
						],
						'resolve' => function($root, $args) {
							return $this->getSingleData(Relay::fromGlobalId($args['id'])['id']);
						}
					]
				];
			};
		}
	}

	private function getTotal() {
		$sql = "SELECT count(*) as count FROM arimanager";;
		$sth = $this->freepbx->Database->prepare($sql);
		$sth->execute();
		return $sth->fetchColumn();
	}

	private function getData($after, $first) {
		$sql = 'SELECT * FROM arimanager';
		$sql .= " " . (!empty($first) && is_numeric($first) ? "LIMIT ".$first : "LIMIT 18446744073709551610");
		$sql .= " " . (!empty($after) && is_numeric($after) ? "OFFSET ".$after : "OFFSET 0");

		$sth = $this->freepbx->Database->prepare($sql);
		$sth->execute();
		return $sth->fetchAll(\PDO::FETCH_ASSOC);
	}

	private function getSingleData($id) {
		$sth = $this->freepbx->Database->prepare('SELECT * FROM arimanager WHERE `id` = :id');
		$sth->execute([
			":id" => $id
		]);
		return $sth->fetch(\PDO::FETCH_ASSOC);
	}

	public function initializeTypes() {
		$user = $this->typeContainer->create('ariManagerUser');
		$user->setDescription('%description%');

		$user->addInterfaceCallback(function() {
			return [$this->getNodeDefinition()['nodeInterface']];
		});

		$user->setGetNodeCallback(function($id) {
			return $this->getSingleData($id);
		});

		$user->addFieldCallback(function() {
			return [
				'id' => Relay::globalIdField('', function($row) {
					return isset($row['id']) ? $row['id'] : null;
				}),
				'arimanager_id' => [
					'type' => Type::nonNull(Type::string()),
					'description' => '',
					'resolve' => function($row) {
						return isset($row['id']) ? $row['id'] : null;
					}
				],
				'name' => [
					'type' => Type::nonNull(Type::string()),
					'description' => 'Asterisk REST Interface User Name',
				],
				'password' => [
					'type' => Type::string(),
					'description' => 'Asterisk REST Inferface Password.',
				],
				'password_format' => [
					'type' => Type::string(),
					'description' => 'For the security consious, you probably don\'t want to put plaintext passwords in the configuration file. ARI supports the use of crypt(3) for password storage.',
				],
				'read_only' => [
					'type' => Type::boolean(),
					'description' => 'Set to Yes for read-only applications.',
				],

			];
		});

		$user->setConnectionResolveNode(function ($edge) {
			return $edge['node'];
		});

		$user->setConnectionFields(function() {
			return [
				'totalCount' => [
					'type' => Type::int(),
					'resolve' => function($value) {
						return $this->getTotal();
					}
				],
				'ariManagerUsers' => [
					'type' => Type::listOf($this->typeContainer->get('ariManagerUser')->getObject()),
					'resolve' => function($root, $args) {
						$data = array_map(function($row){
							return $row['node'];
						},$root['edges']);
						return $data;
					}
				]
			];
		});
	}

	private function getMutationFields() {
		return [
			'id' => [
				'type' => Type::nonNull(Type::id()),
				'description' => ''
			],
			'name' => [
				'type' => Type::nonNull(Type::string()),
				'description' => 'Asterisk REST Interface User Name'
			],
			'password' => [
				'type' => Type::string(),
				'description' => 'Asterisk REST Inferface Password.'
			],
			'password_format' => [
				'type' => Type::string(),
				'description' => 'For the security consious, you probably don\'t want to put plaintext passwords in the configuration file. ARI supports the use of crypt(3) for password storage.'
			],
			'read_only' => [
				'type' => Type::boolean(),
				'description' => 'Set to Yes for read-only applications.'
			],

		];
	}

	private function getMutationExecuteArray($input) {
		return [
			":id" => isset($input['id']) ? $input['id'] : '',
			":name" => isset($input['name']) ? $input['name'] : '',
			":password" => isset($input['password']) ? $input['password'] : null,
			":password_format" => isset($input['password_format']) ? $input['password_format'] : null,
			":read_only" => isset($input['read_only']) ? $input['read_only'] : null,

		];
	}
}

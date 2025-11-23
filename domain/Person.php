<?php
/* Person domain object - consistent with database layer */
class Person {
	private $id;
	private $password;
	private $start_date;
	private $first_name;
	private $last_name;
	private $birthday;
	private $street_address;
	private $city;
	private $state;
	private $zip_code;
	private $phone1;
	private $phone1type;
	private $email;
	private $emergency_contact_first_name;
	private $emergency_contact_last_name;
	private $emergency_contact_phone;
	private $emergency_contact_phone_type;
	private $emergency_contact_relation;
	private $type;
	private $status;
	private $notes;

	private $archived;
	private $skills;
	//private $interests;
	//private $event_topic;
	private $topic_summary;
	private $is_community_service_volunteer;
	private $is_new_volunteer;
	private $total_hours_volunteered;
	private $training_level;
	private $access_level;

	private $organization;

	public function __construct(
		$id, $password, $first_name, $last_name, $status, $phone1, $email,
		$archived, $topic_summary,$organization, 
	) {
		$this->id = $id;

		$this->organization = $organization;

		$this->password = $password;

		$this->first_name = $first_name;
		$this->last_name = $last_name;

		$this->status = $status;

		$this->phone1 = $phone1;

		$this->email = $email;

		$this->archived = $archived;

		

		//$this->event_topic = $event_topic;
		$this->topic_summary = $topic_summary;

		// Determine access level:
		// - vmsroot has full access (3)
		// - status 'admin' maps to access level 2 (regular admin)
		// - otherwise default to standard user (1)
		if ($id === 'vmsroot') {
			$this->access_level = 3;
		} elseif (isset($status) && strtolower($status) === 'admin') {
			$this->access_level = 2;
		} else {
			$this->access_level = 1;
		}
	}

	// Getters used by dbPersons and other code
	public function get_id() { return $this->id; }

	public function get_organization() { return $this->organization; }

	public function get_first_name() { return $this->first_name; }
	public function get_last_name() { return $this->last_name; }

	public function get_status() { return $this->status; }

	public function get_phone1() { return $this->phone1; }

	public function get_email() { return $this->email; }

	public function get_archived() { return $this->archived; }

	//public function get_event_topic() { return $this->event_topic; }
	public function get_topic_summary() { return $this->topic_summary; }

	public function get_access_level() { return $this->access_level; }

	public function get_password() { return $this->password; }



}

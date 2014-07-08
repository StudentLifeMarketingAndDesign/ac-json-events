<?php
class JSONDisplayExtension extends DataExtension{
	
	/*
	private function getEventInstance($eventID){
	
	}
	*/
	private function getDates($rawEvent){
		$eventInstances = $rawEvent['event_instances'];
		$eventInstancesArray = new ArrayList();

		foreach($eventInstances as $i => $eventInstance){

			$dateTime = new LocalistDatetime();
			$dateTime->setValue($eventInstances[$i]['event_instance']['start']);

			if(!$dateTime->InPast()){
				$eventInstancesArray->push($dateTime);
			}
		}

		return $eventInstancesArray;
		
	}
		
	/*
	private function getNextDateTime($rawEvent){
		$event_instances = $rawEvent['event_instances'];
		return(strtotime($event_instances[0]['event_instance']['start']));
	}
	*/
	
	private function getDateLink($NextDateTimeVar){	
		/* moved to LocalistDateTime.php */
	}
	
	private function getVenue($venueID){
		$feedURL = LOCALIST_FEED_URL.'places/'.$venueID;
		$rawFeed = file_get_contents($feedURL);
		$venue['places'] = json_decode($rawFeed, TRUE); 
		return $venue['places'];
	}

	public function parseEvent($rawEvent){

	 	$id = new Text('ID');
	 	$id->setValue($rawEvent['id']);
	 	
	 	$title = new Text('Title');
	 	$title->setValue($rawEvent['title']);

	 	$description_text = new HTMLText('Content');
	 	$description_text->setValue($rawEvent['description_text']);

	 	$link = new Text('Localist_url');
	 	$link->setValue($rawEvent['localist_url']);
	 	
	 	$more_info_link = new Text('Info_link');
	 	$more_info_link->setValue($rawEvent['url']);

	 	$facebook_event_link = new Text('facebook_event_id');
	 	$facebook_event_link->setValue($rawEvent['facebook_id']);

	 	$imageURL = new Text('ImageURL');
		$imageURL->setValue($rawEvent['photo_url']);	

		$dates = new ArrayList();
		$dates = $this->getDates($rawEvent);
		
		$cost = new Text('Cost');
		if ($rawEvent['free']) {
			$cost->setValue('Free');
		} else {
			$cost->setValue($rawEvent['ticket_cost']);
		}
		
		$location = new Text('Location');
		if ($rawEvent['room_number']) {
			$room = $rawEvent['room_number'];
			$location->setValue($rawEvent['location'], $room);
		} else {
			$location->setValue($rawEvent['location']);
		}

		$venue = $this->getVenue($rawEvent['venue_id']);
		
		$venueTitle = new Text('VenueTitle');
		if($rawEvent['venue_id']) {
			$venueTitle->setValue($venue['place']['name']);
		}

		$venueLink = new Text('VenueLink');
		if($rawEvent['venue_url']) {
			$venueLink->setValue($rawEvent['venue_url']);
		}
		/*
		// Localist provides a 'sponsored' key, but it's a boolean. so...not what we're looking for here
		$sponsors = new Text('Sponsors');
		if($rawEvent['sponsors']) {
			$sponsors->setValue($rawEvent['sponsors'][0]['name']);
		}
		*/
		
		$latitude = new Text('Latitude');
		$latitude->setValue($rawEvent['geo']['latitude']);
		
		$longitude = new Text('Longitude');
		$longitude->setValue($rawEvent['geo']['longitude']);
		
		$address = new Text('Address');
		$address->setValue($rawEvent['address']);

		$eventTypes = new Text('Event Types');
		if($rawEvent['filters']['event_types']) {
			$eventTypeNames = array_map(function($item) { return $item['name']; }, $rawEvent['filters']['event_types']);
			//$eventTypeNames = array_column($rawEvent['event_types'], 'name');
			$eventTypes->setValue(implode(', ', $eventTypeNames));
		}

		$parsedEvent = new ArrayData(array(
			'ID'				=> $id,
		    'Title'           	=> $title,
		    'Content'			=> $description_text,
		    'Link' 				=> $link,
		    'FacebookEventLink' => $facebook_event_link,
		    'MoreInfoLink' 		=> $more_info_link,
		    'ImageURL'			=> $imageURL,
		    'Dates' 			=> $dates,
		    //'CancelNote' 		=> $cancel_note,
		    'Cost'				=> $cost,
		    'Location'			=> $location,
		    'VenueTitle' 		=> $venueTitle,
		    'VenueLink' 		=> $venueLink,
		    //'Sponsors' 		=> $sponsors,
		    'EventTypes' 		=> $eventTypes,
		    'Latitude'			=> $latitude,
		    'Longitude'			=> $longitude,
		    'Address'			=> $address
	    ));
		
		return $parsedEvent;
	}

	public function parsePlace($rawPlace) {

	 	$id = new Text('ID'); $id->setValue($rawPlace['id']);
	 	
	 	$title = new Text('Title');	$title->setValue($rawPlace['name']);

	 	$description_text = new HTMLText('Content'); $description_text->setValue($rawPlace['description_text']);

	 	$link = new Text('Localist_url'); $link->setValue($rawPlace['localist_url']);

	 	$imageURL = new Text('ImageURL'); $imageURL->setValue($rawPlace['photo_url']);	
				
		/*
		// Localist provides a 'sponsored' key, but it's a boolean. so...not what we're looking for here
		$sponsors = new Text('Sponsors');
		if($rawPlace['sponsors']) {
			$sponsors->setValue($rawPlace['sponsors'][0]['name']);
		}
		*/
		
		$latitude = new Text('Latitude');$latitude->setValue($rawPlace['geo']['latitude']);
		
		$longitude = new Text('Longitude');$longitude->setValue($rawPlace['geo']['longitude']);
		
		$address = new Text('Address');$address->setValue($rawPlace['address']);

		$parsedPlace= new ArrayData(array(
			'ID'				=> $id,
		    'Title'           	=> $title,
		    'Content'			=> $description_text,
		    'Link' 				=> $link,
		    'ImageURL'			=> $imageURL,
		    'Latitude'			=> $latitude,
		    'Longitude'			=> $longitude,
		    'Address'			=> $address
	    ));
		
		return $parsedPlace;
	}


	/*
	public function ACActiveVenues($feedURL = "events/?days=200&pp=50&distinct=true") {
	
		$activeEvents = $this->AfterClassEvents();
		$venues = new ArrayList();
		
		foreach($events as $key => $event){
			$venue = new LocalistVenue();
			$venue->ID = $event->venueID; //venueID needs to be in parse event
			$venues->push($venue);
		}
		
		$venues->removeDuplicates();
		
		
		return $venues;
		//loop through events in /events/
		$feedURL = LOCALIST_FEED_URL.$feedURL;
		$activeVenues = new ArrayList();
		$rawFeed = file_get_contents($feedURL);
		$eventsDecoded = json_decode($rawFeed, TRUE);
		$eventsArray = $eventsDecoded['events'];
		
		//if the venue id is new/unique
			//add venue_id into ass.array as key
			//place event id in it's value as list
		//else if the venue is not unique 
			//then place event id into its value
		// 

		foreach($eventsArray as $event) {
			$activeVenues->push($this->parseEvent($event['event']));
		}		
		return $activeVenues;   

	}
	
		public function ACActiveVenueEvents($id){

		$feedURL = 'http://localhost:8888/localist-api-examples/events.json';
		$feed = new ArrayList();
		$rawFeed = file_get_contents($feedURL);
		$eventsDecoded = json_decode($rawFeed, TRUE);
		$eventsList = $eventsDecoded['events'];
		if(isset($eventsList)){
			//echo('hello, world');
			//print_r ($eventsList);
			foreach($eventsList as $event) {
				//print_r ($event);
				if($event['event']['id'] == $id){
					return $this->parseEvent($event);
				}
			}
		}
		return false;
	}
	*/
	
	//	
	
	public function AfterClassEvents($feedURL = "events/?days=200&pp=50&distinct=true") {
		$feedURL = LOCALIST_FEED_URL.$feedURL;
		$eventsList = new ArrayList();
		$rawFeed = file_get_contents($feedURL);
		$eventsDecoded = json_decode($rawFeed, TRUE);
		$eventsArray = $eventsDecoded['events'];
		foreach($eventsArray as $event) {
			$eventsList->push($this->parseEvent($event['event']));
		}		
		return $eventsList;   
	}
	
	public function AfterClassEvent($id){
		$feedParams = "events/".$id;
		$feedURL = LOCALIST_FEED_URL.$feedParams;
		$feed = new ArrayList();
		$rawFeed = file_get_contents($feedURL);
		$eventsDecoded = json_decode($rawFeed, TRUE);

		$event = $eventsDecoded['event'];
		if(isset($event)){
			return $this->parseEvent($event);	
		}
		return false;
	}
	
}
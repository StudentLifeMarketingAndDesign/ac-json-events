<?php
class JSONDisplayExtension extends DataExtension{

	private function parseEvent($rawEvent){

	 	$id = new Text('ID');
	 	$id->setValue($rawEvent['id']);
	 	
	 	$title = new Text('Title');
	 	$title->setValue($rawEvent['title']);

	 	$link = new Text('Link');
	 	$link->setValue($rawEvent['link']);
	 	
	 	$more_info_link = new Text('more_info_link');
	 	$more_info_link->setValue($rawEvent['more_info_link']);

	 	$facebook_event_link = new Text('facebook_event_link');
	 	$facebook_event_link->setValue($rawEvent['facebook_event_link']);

	 	$imageURL = new Text('ImageURL');
		$imageURL->setValue($rawEvent['photo_url']);	

		$cancel_note = new Text('cancel_note');
		$cancel_note->setValue($rawEvent['cancel_note']);
	 	
		$nextDateTime = new SS_Datetime('NextDateTime');
		//$nextDateTime->setValue(strtotime($rawEvent['dates'][0]['start_date'].' '.$rawEvent['dates'][0]['start_time']));
		$time = time();
		
		foreach($rawEvent['event_instances'] as $eventInstance) {
			//print_r (strtotime(substr($eventInstance['event_instance']['start'], 0, 10)));
			if (strtotime(substr($eventInstance['event_instance']['start'], 0, 10)) >= $time) {
				$nextDateTime->setValue($eventInstance['event_instance']['start']);
				break;
			} else {
				print_r ('nothing found');
			}		
		};
		
		$dateTimeCount = new Int('DateTimeCount');
		$dateTimeCount->setValue(count($rawEvent['dates']));

		$cost = new Text('Cost');
		$cost->setValue($rawEvent['price']);
			
		$location = new Text('Location');
		$location->setValue($rawEvent['location']);

		$venue = new Text('Venue');
		if($rawEvent['venues']) {
			$venue->setValue($rawEvent['venues'][0]['name']);
		}

		$sponsors = new Text('Sponsors');
		if($rawEvent['sponsors']) {
			$sponsors->setValue($rawEvent['sponsors'][0]['name']);
		}

		$eventTypes = new Text('Event Types');
		if($rawEvent['event_types']) {
			$eventTypeNames = array_map(function($item) { return $item['name']; }, $rawEvent['event_types']);
			//$eventTypeNames = array_column($rawEvent['event_types'], 'name');
			$eventTypes->setValue(implode(', ', $eventTypeNames));
		}

		$parsedEvent = new ArrayData(array(
			'ID'				=> $id,
		    'Title'         	=> $title,
		    'Link' 				=> $link,
		    'FacebookEventLink' => $facebook_event_link,
		    'MoreInfoLink' 		=> $more_info_link,
		    'ImageURL'			=> $imageURL,
		    'CancelNote' 		=> $cancel_note,
		    'NextDateTime'		=> $nextDateTime,
		    'DateTimeCount'		=> $dateTimeCount,
		    'Cost'				=> $cost,
		    'Location'			=> $location,
		    'Venue' 			=> $venue,
		    'Sponsors' 			=> $sponsors,
		    'EventTypes' 		=> $eventTypes
	    ));
		return $parsedEvent;
	}
		
	public function AfterClassEvents($feedURL="http://hulk.imu.uiowa.edu/localist-api-examples/events.json") {
		
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

		$feedURL = 'http://hulk.imu.uiowa.edu/localist-api-examples/events.json';
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
	
}
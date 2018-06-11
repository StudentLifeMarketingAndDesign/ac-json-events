<?php
class UiCalendarEventType extends DataObject {

	private static $db = array(
		"Title"		=> "Varchar(255)"
	);

	/*public function parseTag($venueDecoded){
		if(isset($venueDecoded['place'])){
			$venueDecoded = $venueDecoded['place'];
			$this->ID = $venueDecoded['id'];
			$this->Title = $venueDecoded['name'];
			$this->Content = $venueDecoded['description_text'];
			$this->PageList = Page::get();
			$this->ImageURL = $venueDecoded['photo_url'];
			$this->UiCalendarLink = $venueDecoded['localist_url'];
			$this->WebsiteLink = $venueDecoded['url'];
			$this->Latitude = $venueDecoded['geo']['latitude'];
			$this->Longitude = $venueDecoded['geo']['longitude'];
			$this->Address = $venueDecoded['address'];

			return $this;
		}
	}*/

	public function parseType($rawType){
		$localistType = new UiCalendarEventType();
		$localistType->ID = $rawType['id'];
		$localistType->Title = $rawType['name'];
		//$localistType->UiCalendarLink = $rawType['localist_url'];
		$localistType->UiCalendarLink = UICALENDAR_BASE.'search/events/?event_types='.$localistType->ID;
		return $localistType;

	}

	public function Link(){
		$calendar = UiCalendar::getOrCreate();
		if($calendar->IsInDB()){
			return $calendar->Link().'type/'.$this->ID;
		}
		return $this->UiCalendarLink;

	}

	
	public function EventList() {
		//echo "type: <br />";
		//print_r($this->ID);
		//echo "<br />";

		$calendar = UiCalendar::getOrCreate();

		//print_r($calendar);
		$events = $calendar->EventList(200, $startDate = NULL, $endDate = NULL, $venue = null, $keyword = null, $type = $this->ID);
		//print_r($events);

		$eventsAtTypeList = new ArrayList();
		//print_r($events);
		if(!isset($events)){
			return false;
		}


		foreach($events as $event) {
			$eventsAtTypeList->push($event);
		}		
 
		return $events;   
	}
	/*public function Link(){
		$calendar = UiCalendar::getOrCreate();
		$link = $calendar->Link().'event/'.$this->ID;
		return $link;
	}*/

}
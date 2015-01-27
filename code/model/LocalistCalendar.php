<?php
class LocalistCalendar extends Page {

	private static $db = array(
		'EventTypeFilterID' => 'Int',
		'DepartmentFilterID'=> 'Int',
		'VenueFilterID' 	=> 'Int',
		'GeneralInterestFilterID' => 'Int',

		'FeaturedEvent1ID' => 'Int',
		'FeaturedEvent2ID' => 'Int',
		'FeaturedEvent3ID' => 'Int',
		'FeaturedEvent4ID' => 'Int',

	);

	private static $has_one = array(

	);

	private static $allowed_children = array( '' );
	private static $icon = 'ac-json-events/images/calendar-file.png';

	function getCMSFields() {
		$fields = parent::getCMSFields();

		$types = $this->TypeList();
		$typesArray = $types->map();

		$departments = $this->DepartmentList();
		$departmentsArray = $departments->map();

		$venues = $this->VenuesList();
		$venuesArray = $venues->map();

		$genInterests = $this->GeneralInterestList();
		$genInterestsArray = $genInterests->map();

		//print_r($genInterestsArray);

		$typeListBoxField = new DropdownField( 'EventTypeFilterID', 'Filter the calendar by this Localist event type:', $typesArray );
		$typeListBoxField->setEmptyString( '(No Filter)' );

		$departmentDropDownField = new DropdownField( 'DepartmentFilterID', 'Filter the calendar by this Localist department', $departmentsArray );
		$departmentDropDownField->setEmptyString( '(No Filter)' );

		$venueDropDownField = new DropdownField( 'VenueFilterID', 'Filter the calendar by this Localist Venue', $venuesArray );
		$venueDropDownField->setEmptyString( '(No Filter)' );

		$genInterestDropDownField = new DropdownField( 'GeneralInterestFilterID', 'Filter the calendar by this Localist General Interest', $genInterestsArray);
		$genInterestDropDownField->setEmptyString( '(No Filter)' );

		$fields->addFieldToTab( 'Root.Main', $typeListBoxField, 'Content' );
		$fields->addFieldToTab(' Root.Main', $departmentDropDownField, 'Content' );
		$fields->addFieldToTab(' Root.Main', $venueDropDownField, 'Content' );
		$fields->addFieldToTab(' Root.Main', $genInterestDropDownField, 'Content' );
		$fields->removeByName( 'Content' );
		$fields->removeByName( 'Metadata' );

		$events = $this->EventList();

		if ($events->First()) {
			$eventsArray = $events->map();

			$fields->addFieldToTab(' Root.Main', new LabelField('FeaturedEventFieldLabel', 'If no featured events are selected below, events marked at "Featured" in Localist will be used.' ));

			$featuredEvent1Field = new DropdownField( "FeaturedEvent1ID", "Featured Event 1", $eventsArray );
			$featuredEvent1Field->setEmptyString( '(No Event)' );
			$fields->addFieldToTab( 'Root.Main', $featuredEvent1Field );

			$featuredEvent2Field = new DropdownField( "FeaturedEvent2ID", "Featured Event 2", $eventsArray );
			$featuredEvent2Field->setEmptyString( '(No Event)' );
			$fields->addFieldToTab( 'Root.Main', $featuredEvent2Field );

			$featuredEvent3Field = new DropdownField( "FeaturedEvent3ID", "Featured Event 3", $eventsArray );
			$featuredEvent3Field->setEmptyString( '(No Event)' );
			$fields->addFieldToTab( 'Root.Main', $featuredEvent3Field );

			$featuredEvent4Field = new DropdownField( "FeaturedEvent4ID", "Featured Event 4", $eventsArray );
			$featuredEvent4Field->setEmptyString( '(No Event)' );
			$fields->addFieldToTab( 'Root.Main', $featuredEvent4Field );

		}


		return $fields;
	}

	/**
	 * Generates an ArrayList of Featured Events by using the calendar's FeaturedEvent IDs.
	 * TODO: Check for the existence of the events in the API first before pushing them to the 
	 * ArrayList.
	 * TODO: Make sure the event has upcoming dates before pushing it into the ArrayList
	 * @return ArrayList
	 */
	public function FeaturedEvents() {

		$events = $this->EventList();
		$featuredEvents = new ArrayList();

		//Get featured events from SilverStripe if there are any.

		if($this->FeaturedEvent1ID != 0){
			if($this->SingleEvent( $this->FeaturedEvent1ID)){
				$featuredEvents->push( $this->SingleEvent( $this->FeaturedEvent1ID ) );
			}
		}
		if($this->FeaturedEvent2ID != 0){
			if($this->SingleEvent( $this->FeaturedEvent2ID)){
				$featuredEvents->push( $this->SingleEvent( $this->FeaturedEvent2ID ) );
			}
		}
		if($this->FeaturedEvent3ID != 0){
			if($this->SingleEvent( $this->FeaturedEvent3ID)){
				$featuredEvents->push( $this->SingleEvent( $this->FeaturedEvent3ID ) );
			}
		}
		if($this->FeaturedEvent4ID != 0){
			if($this->SingleEvent( $this->FeaturedEvent4ID)){
				$featuredEvents->push( $this->SingleEvent( $this->FeaturedEvent4ID ) );
			}
		}

		//If there aren't any featured events selected in SilverStripe, fall back on events marked as featured in Localist. 
		if($featuredEvents->count() > 0){
			return $featuredEvents;
		}else{
			foreach ( $events as $event ) {
				if($event->Featured == 'true'){
					$featuredEvents->push($event);
				}
			}		
		}

		if($featuredEvents->First()){
			return $featuredEvents;
		}else{
			return false;
		}

	}

	/**
	 * Returns a Calendar Widget for the template.
	 * @return CalendarWidget
	 */

	public function CalendarWidget() {
		$calendar = CalendarWidget::create( $this );
		return $calendar;
	}

	/**
	 * Returns an ArrayList of Trending Tags sorted by popularity.
	 * @return ArrayList
	 */
	public function TrendingTags() {
		$events = $this->EventList();
		$tags = array();
		$localistTags = new ArrayList();

		if($events->First()){
			foreach ( $events as $event ) {

				foreach ( $event->Tags as $eventTag ) {
					if ( isset( $tags[$eventTag->Title] ) ) {
						$tags[$eventTag->Title] = $tags[$eventTag->Title] + 1;
					}else {
						$tags[$eventTag->Title] = 0;
					}
				}
			}

			arsort( $tags );

			foreach ( $tags as $key => $tag ) {
				$localistTag = new LocalistTag();
				$localistTag->Title = $key;
				$localistTags->push( $localistTag );

			}

			return $localistTags;
		}else{
			return false;
		}

	}

	/**
	 * Returns an ArrayList of Trending Types sorted by popularity.
	 * @return ArrayList
	 */
	public function TrendingTypes(){
		$events = $this->EventList();
		$types = array();

		if($events->First()){
			$localistEventTypes = new ArrayList();
			foreach ( $events as $event ) {
				if($event->Types && $event->Types->First()){
					foreach ( $event->Types as $eventType ) {
						if ( isset( $types[$eventType->ID] ) ) {
							$types[$eventType->ID] = $types[$eventType->ID] + 1;
						}else {
							$types[$eventType->ID] = 0;
						}
					}
				}
			}

			arsort( $types );
			foreach ( $types as $key => $type ) {

				$localistEventType = $this->getTypeByID($key);
				$localistEventTypes->push( $localistEventType );
			}
			
			return $localistEventTypes;
		}else{
			return false;
		}
	}

	public function requestAllPages($feedURL, $resourceName) {
		$page = 1;
		$pp = 100;
		$info = $this->getJson($feedURL.'?pp='.$pp.'&page='.$page);
		$fullPageList = $info;
		
		if (isset($info['page']['total'])) {
			$numOfPages = $info['page']['total'];		
			for ($page; $page <= $numOfPages; $page++) {
				$thisPage = $this->getJson($feedURL.'?pp='.$pp.'&page='.$page);
				foreach ($thisPage[$resourceName] as $key => $value) {
					array_push($fullPageList[$resourceName], $thisPage[$resourceName][$key]);
				}
				if ($page > 999) { //failsafe for infinite loops
					break;
				}
			}
		} else {
			return $fullPageList;
		}
		
		return $fullPageList;
	}

	/**
	 * Returns an ArrayList of all venues that are coming through our main EventList function
	 * @return ArrayList
	 */
	public function ActiveVenueList() {
		$activeEvents = $this->EventList();
		$venuesList = new ArrayList();

		foreach ( $activeEvents as $key => $parsedEvent ) {

			if ( $parsedEvent->Venue->ID != 0 ) {
				$venuesList->push( $parsedEvent->Venue );
				}
		}

		$venuesList->removeDuplicates();
		
		return $venuesList;
	}
	
	public function VenuesList() {
		$resourceName = "places";
		$feedURL = LOCALIST_FEED_URL.$resourceName;
		$venuesList = new ArrayList();

		//$venuesDecoded = $this->getJson($feedURL);
		$venuesDecoded = $this->requestAllPages($feedURL, $resourceName);
		$venuesArray = $venuesDecoded[$resourceName];
		//print_r($venuesArray);

		if ( isset ( $venuesArray ) ) {
			foreach ( $venuesArray as $venue ) {
				$localistVenues = new LocalistVenue();
				$localistVenue = $localistVenues->parseVenue( $venue );
				$venuesList->push($localistVenue);
			}
		}
		
		return $venuesList;
	}

	/**
	 * Returns an ArrayList of all LocalistEventTypes based on the events coming through EventList()
	 * @return ArrayList
	 */
	public function TypeList() {

		$resourceName = 'event_types';
		$feedURL = LOCALIST_FEED_URL.'events/filters';

		$typesList = new ArrayList();

		$typesDecoded = $this->getJson($feedURL);
		//$typesDecoded = $this->requestAllPages($feedURL, $resourceName);
		$typesArray = $typesDecoded[$resourceName];

		if ( isset( $typesArray ) ) {
			foreach ( $typesArray as $type ) {
				$localistType = new LocalistEventType();
				$localistType = $localistType->parseType( $type );
				$typesList->push( $localistType );
			}
		}

		return $typesList;
	}

	public function DepartmentList() {
		$cache = new SimpleCache();
		$feedURL = LOCALIST_FEED_URL.'events/filters/';

		$departmentsList = new ArrayList();

		$rawFeed = $cache->get_data( $feedURL, $feedURL );
		$departmentsDecoded = json_decode( $rawFeed, TRUE );

		if ( isset($departmentsDecoded['departments'])) {
			$departmentsArray = $departmentsDecoded['departments'];
		}

		if ( isset( $departmentsArray ) ) {
			foreach ( $departmentsArray as $department ) {
				$localistDepartment = new LocalistEventType();
				$localistDepartment = $localistDepartment->parseType( $department );
				$departmentsList->push( $localistDepartment );
			}
		}

		return $departmentsList;

	}

	public function GeneralInterestList() {

		$cache = new SimpleCache();
		$feedURL = LOCALIST_FEED_URL.'events/filters/';

		$genInterestsList = new ArrayList();

		$rawFeed = $cache->get_data( $feedURL, $feedURL );
		$genInterestsDecoded = json_decode( $rawFeed, TRUE );

		if ( isset($genInterestsDecoded['event_general_interest'])) {
			$genInterestsArray = $genInterestsDecoded['event_general_interest'];
		}

		//print_r($genInterestsArray);
		if ( isset( $genInterestsArray ) ) {
			foreach ( $genInterestsArray as $genInterest ) {
				$localistGenInterest = new LocalistEventType();
				$localistGenInterest = $localistGenInterest->parseType( $genInterest );
				$genInterestsList->push( $localistGenInterest );
			}
		}

		//print_r($genInterestsList);

		return $genInterestsList;

	}
	
	/**
	 * Finds a specific event type by checking the master TypeList() and matching the ID against
	 * all types. 
	 * TODO: More effecient way to do this? Through the API?
	 * @param int $id 
	 * @return LocalistEventType
	 */
	public function getTypeByID( $id ) {
		$types = $this->TypeList();

		foreach ( $types as $type ) {
			if ( $type->ID == $id ) {
				return $type;
			}
		}

		return false;
	}
	public function getTagByID( $id ) {
		$types = $this->TagList();

		foreach ( $types as $type ) {
			if ( $type->ID == $id ) {
				return $type;
			}
		}

		return false;
	}
	public function getVenueByID( $id ) {
		$venues = $this->ActiveVenueList();
		
		foreach ( $venues as $venue ) {
			if ( isset( $venue ) ) {
				if ( $venue->ID == $id ) {
					return $venue;
				}
			}
		}
		return false;
		
	}
	
	public function getTodayEvents() {
		$start = sfDate::getInstance();

		$events = $this->EventList( 200, $start->format('Y-m-d'), $start->add(1)->format('Y-m-d'));
		return $events;
	}

	public function getWeekendEvents() {
		$start = sfDate::getInstance();

		if($start->format('w') == sfTime::SATURDAY) {
			$start->yesterday();
		}
		elseif($start->format('w') != sfTime::FRIDAY) {
			$start->nextDay(sfTime::FRIDAY);
		}
		$end = sfDate::getInstance($start)->nextDay(sfTime::SUNDAY);

		$startDate = $start->format('Y-m-d');
		$endDate = $end->format('Y-m-d');

		$events = $this->EventList( 200, $startDate, $endDate );
		return $events;
	}

	public function getMonthEvents() {
		$startDate = sfDate::getInstance()->firstDayOfMonth()->format( 'Y-m-d' );
		$endDate = sfDate::getInstance( $this->startDate )->finalDayOfMonth()->format( 'Y-m-d' );

		$events = $this->EventList( 200, $startDate, $endDate );
		return $events;
	}

	public function getJson($feedURL){
		$cache = new SimpleCache();
		if($rawFeed = $cache->get_data( $feedURL, $feedURL )){
	    	$eventsDecoded = json_decode( $rawFeed, TRUE );	
		} else {
		    $rawFeed = $cache->do_curl($feedURL);
		    $cache->set_cache($feedURL, $rawFeed);
			$eventsDecoded = json_decode( $rawFeed, TRUE );
		}
		
		if (!empty($eventsDecoded)) {
			return $eventsDecoded;
		} else {
			return false;
		}
		
	}

	/**
	 * Produces a list of Events based on a number of factors, used in templates
	 * and as a helper function in this class and others.
	 * 
	 * @param int $days 
	 * @param string $startDate 
	 * @param string $endDate 
	 * @param int $venue 
	 * @param string $keyword 
	 * @param int $type 
	 * @param boolean $distinct
	 * @return ArrayList
	 */

	public function EventList( 
		$days = '200', 
		$startDate = null, 
		$endDate = null, 
		$venue = null, 
		$keyword = null,
		$type = null, 
		$distinct = 'true', 
		$enableFilter = true, 
		$searchTerm = null ) {

		if($enableFilter){
			if ($this->EventTypeFilterID != 0) {
				$primaryFilterTypeID = $this->EventTypeFilterID;
			} 
			if ($this->DepartmentFilterID != 0) {
				$departmentFilterID = $this->DepartmentFilterID;
			}
			if ($this->VenueFilterID != 0) {
				$venueFilterID = $this->VenueFilterID;
			}
			if ($this->GeneralInterestFilterID != 0) {
				$genInterestFilterID = $this->GeneralInterestFilterID;
			}
		}

		$feedParams = '';

		if(isset($searchTerm)){
			$feedParams = '/search';
		}
		$feedParams .= '?';
		$feedParams .= 'days='.$days;
		
		$startDateSS = new SS_Datetime();
		$endDateSS = new SS_Datetime();

		if ( isset( $startDate ) ) {
			$startDateSS->setValue( $startDate );
			$feedParams .= '&start='.$startDateSS->format( 'Y-m-d' );
		}
		if ( isset( $endDate ) ) {
			$endDateSS->setValue( $endDate );
			$feedParams .= '&end='.$endDateSS->format( 'Y-m-d' );
		}

		if ( isset( $venue ) ) {
			$feedParams .= '&venue_id='.$venue;
		}

		if(!isset($searchTerm)){
			if ( isset( $keyword ) ) {
				$feedParams .= '&keyword='.$keyword;
			}
		}
		if ( isset( $type ) ) {
			$feedParams .= '&type[]='.$type;
		}

		if ( isset( $primaryFilterTypeID ) ) {
			$feedParams .= '&type[]='.$primaryFilterTypeID;
		}
		
		if ( isset( $departmentFilterID ) ) {
			$feedParams .= '&type[]='.$departmentFilterID;
		}
		if ( isset( $genInterestFilterID ) ) {
			$feedParams .= '&type[]='.$genInterestFilterID;
		}
		
		if ( isset( $venueFilterID ) ) {
			$feedParams .= '&venue_id='.$venueFilterID;
		}	
		if ( isset( $searchTerm ) ) {
			$feedParams .= '&search='.$searchTerm;
		}
		$feedParams .= '&pp=100&match=all&distinct='.$distinct;
		$feedURL = LOCALIST_FEED_URL.'events'.$feedParams;

		//print_r($feedURL);

		$eventsList = new ArrayList();
		$eventsDecoded = $this->getJson($feedURL);

		if(isset($eventsDecoded['events'])){
			$eventsArray = $eventsDecoded['events'];
			foreach ( $eventsArray as $event ) {
				if ( isset( $event ) ) {
					$localistEvent = new LocalistEvent();

					$eventsList->push( $localistEvent->parseEvent( $event['event'] ) );
				}
			}
			return $eventsList;
		}
		
	}

	/**
	 * Gets a single event from the Localist Feed based on ID.
	 * @param int $id 
	 * @return LocalistEvent
	 */

	public function SingleEvent( $id, $mustBeUpcoming = true ) {
		if(!isset($id) || $id == 0) return false;

		$feedParams = 'events/'.$id;
		$feedURL = LOCALIST_FEED_URL.$feedParams;

		$eventsDecoded = $this->getJson($feedURL);
		$event = $eventsDecoded['event'];
		if ( isset( $event ) ) {
			$localistEvent = new LocalistEvent();
			$parsedEvent = $localistEvent->parseEvent( $event );
			//If we're only looking for an event with upcoming dates, check the event's Dates and return the event if there are any.
			if($mustBeUpcoming){
				if($parsedEvent->Dates->count() > 0){
					return $parsedEvent;
				}else{
					return false;
				}
			}else{
				return $parsedEvent;
			}
		}
		return false;
	}


}
class LocalistCalendar_Controller extends Page_Controller {

	/**
	 * An array of actions that can be accessed via a request. Each array element should be an action name, and the
	 * permissions or conditions required to allow the user to access it.
	 *
	 * <code>
	 * array (
	 *     'action', // anyone can access this action
	 *     'action' => true, // same as above
	 *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
	 *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
	 * );
	 * </code>
	 *
	 * @var array
	 */
	private static $allowed_actions = array (
		'event',
		'show',
		'monthjson',
		'tag',
		'type',
		'venue',
		'search',

		//legacy feed actions
		'feed'
	);


	/** URL handlers / routes  
	 */
	private static $url_handlers = array(
		'event/$eventID' => 'event',
		'show/$startDate/$endDate' => 'show',
		'monthjson/$ID' => 'monthjson',
		'tag/$tag' => 'tag',
		'type/$type' => 'type',
		'venue/$venue' => 'venue',
		'search' => 'search',

		//legacy feed urls:

		'feed/$Type' => 'Feed',
	);
	

	public function index(){

		$startDate = new SS_Datetime();
		$endDate = new SS_Datetime();

		$startDate->setValue(date('Y-m-d'));
		$endDate->setValue(strtotime($startDate." +200 days"));

		$filterHeader = $startDate->format('F j').' - '.$endDate->format('F j');

		$Data = array (
			'FilterHeader' => $filterHeader
		);

		return $this->customise($Data)->renderWith(array('LocalistCalendar', 'Page'));


	}

	/**
	 * Controller function that renders a single event through a $url_handlers route.
	 * @param SS_HTTPRequest $request 
	 * @return Controller
	 */
	public function event( $request ) {
		$eventID = addslashes( $this->urlParams['eventID'] );

		/* If we're using an event ID as a key. */
		if ( is_numeric( $eventID ) ) {
			$event = $this->SingleEvent( $eventID );
			return $event->renderWith( array( 'LocalistEvent', 'Page' ) );
		}else {

			/* Getting an event based on the url slug **EXPERIMENTAL ** */
			$events = $this->EventList();
			foreach ( $events as $key => $e ) {
				if ( $e->URLSegment == $eventID ) {
					//print_r($e->URLSegment);
					$singleEvent = $this->SingleEvent($e->ID);
					return $this->customise( $singleEvent )->renderWith( array( 'LocalistEvent', 'Page' ) );;
				}
			}

		
				
		}
		//echo "hello";
		$this->Redirect(LOCALIST_BASE.'event/'.$eventID);
		//return $this->httpError( 404, 'The requested event can\'t be found in the events.uiowa.edu upcoming events list.');

	}

	/**
	 * Controller function that filters the calendar by a start+end date or a human-readable string like 'weekend'
	 * @param SS_HTTPRequest $request 
	 * @return Controller
	 */
	public function show( $request ) {

		$dateFilter = addslashes( $this->urlParams['startDate'] );

		switch ( $dateFilter ) {
		case 'weekend':
			$events = $this->getWeekendEvents();
			$filterHeader = 'This weekend:';
			break;
		case 'today':
			$events = $this->getTodayEvents();
			$filterHeader = 'Today:';
			break;
		case 'month':
			$events = $this->getMonthEvents();
			$filterHeader = 'This month:';
			break;
		default:
			$startDate = new SS_Datetime();
			$startDate->setValue( addslashes( $this->urlParams['startDate'] ) );

			$endDate = new SS_Datetime();
			$endDate->setValue( addslashes( $this->urlParams['endDate'] ) );
			$filterHeader = $startDate->format( 'l, F j' );

			if ( $endDate->getValue() ) {
				$filterHeader .= ' to '.$endDate->format( 'l, F j' );
			}


			$events = $this->EventList( null, $startDate->format( 'l, F j' ), $endDate->format( 'l, F j' ) );

		}

		$Data = array (
			'EventList' => $events,
			'FilterHeader' => $filterHeader,
		);
		return $this->customise( $Data )->renderWith( array( 'LocalistCalendar', 'Page' ) );

	}
	
	/**
	 * Controller Function that renders a filtered Event List by a Localist tag or keyword.
	 * @param SS_HTTPRequest $request 
	 * @return Controller
	 */
	public function tag( $request ) {
		$tagName = addslashes( $this->urlParams['tag'] );
		$events = $this->EventList( 200, null, null, null, rawurlencode($tagName) );
		$filterHeader = 'Events tagged as "'.$tagName.'":';

		$Data = array (
			'Title' => $tagName.' | '.$this->Title,
			'EventList' => $events,
			'FilterHeader' => $filterHeader,
		);

		return $this->customise( $Data )->renderWith( array( 'LocalistCalendar', 'Page' ) );
	}

	public function type( $request ) {
		$typeID = addslashes( $this->urlParams['type'] );
		$type = $this->getTypeByID( $typeID );

		$events = $this->EventList( 200, null, null, null, null, $type->ID );

		$filterHeader = 'Events categorized as "'.$type->Title.'":';

		$Data = array (
			'Title' => $type->Title.' | '.$this->Title,
			'EventList' => $events,
			'FilterHeader' => $filterHeader,
		);

		return $this->customise( $Data )->renderWith( array( 'LocalistCalendar', 'Page' ) );
	}

	public function venue( $request ) {
		$venueID = addslashes( $this->urlParams['venue'] );
		$venue = $this->getVenueByID( $venueID );

		if(isset($venue)){
			$events = $this->EventList( 200, null, null, $venue->ID );

			$filterHeader = 'Events listed at '.$venue->Title.':';

			$Data = array (
				'Title' => $venue->Title.' | '.$this->Title,
				'Venue' => $venue,
				'EventList' => $events,
				'FilterHeader' => $filterHeader,
			);

			return $this->customise( $Data )->renderWith( array( 'LocalistVenue', 'LocalistCalendar', 'Page' ) );
		}else{
			return $this->httpError( 404, 'The requested venue can\'t be found in the events.uiowa.edu upcoming events list.');
		}
	}

	public function search($request){
		$term = $request->getVar('term');
		//print_r('term: '.$term);
		$events = $this->EventList('200', null, null, null, null,null, 'true', false, $term);

		$data = array( 
			"Results" => $events,
			"Term" => $term
		);

		return $this->customise( $data )->renderWith( array( 'LocalistCalendar_search', 'Page' ) );

	}

	public function monthjson( $r ) {
		if ( !$r->param( 'ID' ) ) return false;
		$this->startDate = sfDate::getInstance( CalendarUtil::get_date_from_string( $r->param( 'ID' ) ) );
		$this->endDate = sfDate::getInstance( $this->startDate )->finalDayOfMonth();

		$json = array ();
		$counter = clone $this->startDate;
		while ( $counter->get() <= $this->endDate->get() ) {
			$d = $counter->format( 'Y-m-d' );
			$json[$d] = array (
				'events' => array ()
			);
			$counter->tomorrow();
		}
		$list = $this->EventList();
		foreach ( $list as $e ) {
			//print_r($e->Dates);
			foreach ( $e->Dates as $date ) {
				if ( isset( $json[$date->StartDateTime->Format( 'Y-m-d' )] ) ) {
					$json[$date->StartDateTime->Format( 'Y-m-d' )]['events'][] = $e->getTitle();
				}
			}
		}
		return Convert::array2json( $json );
	}

	/*******************************************/
	/* RSS And JSON Feed Methods are now DEAD. */
	/*******************************************/	

 	public function Feed(){
 		$this->redirect("events/");
 	}
 	

}
?>

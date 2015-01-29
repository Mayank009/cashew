<?php namespace Owlgrin\Cashew\Hooks;

use Illuminate\Support\Facades\Event as IlluminateEvent;
use Owlgrin\Cashew\Storage\Storage;
use Owlgrin\Cashew\Event\Event;
use Carbon;
/**
 * Hook to handle created inovice.
 */
class InvoiceCreateHook implements Hook {

	/**
	 * Instance of storage implementation
	 * @var Storage
	 */
	protected $storage;

	public function __construct(Storage $storage)
	{
		$this->storage = $storage;
	}

	/**
	 * Handles the event
	 * @param  Event  $event
	 * @return void
	 */
	public function handle(Event $event)
	{
		$subscription = $this->storage->subscription($event->customer(), true);

		// if( ! is_null($subscription['trial_ends_at']))
		// {
		// 	$trialEndsAt = Carbon::createFromFormat('Y-m-d H:i:s', $subscription['trial_ends_at'])->startOfDay();
		// 	$invoiceDate = Carbon::createFromTimestamp($event->invoice()->date())->startOfDay();
			
		// 	if( ! $trialEndsAt->lt($invoiceDate)) return;
		// }
		
		IlluminateEvent::fire('cashew.invoice.created', array($subscription['user_id'], $event->invoice()));
	}
}
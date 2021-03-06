<?php namespace Owlgrin\Cashew\Storage;

use Owlgrin\Cashew\Storage\Storage;
use Owlgrin\Cashew\Customer\Customer;
use Owlgrin\Cashew\Subscription\Subscription;
use Owlgrin\Cashew\Invoice\Invoice;
use Owlgrin\Cashew\Invoice\LocalInvoice;
use Owlgrin\Cashew\Card\Card;
use Carbon\Carbon, Config, DB;

/**
 * The database implementation of Storage
 */
class DbStorage implements Storage {

	/**
	 * Returns the subscription
	 * @param  integer  $id
	 * @param  boolean $byCustomer
	 * @return array
	 */
	public function subscription($id, $byCustomer = false)
	{
		if( ! $id) throw new \Exception('Cannot fetch subscription');

		return $byCustomer ? $this->subscriptionByCustomer($id) : $this->subscriptionByUser($id);
	}

	/**
	 * Creates a new subscription
	 * @param  string   $userId
	 * @param  Customer $customer
	 * @return integer
	 */
	public function create($userId, Customer $customer)
	{
		$id = DB::table(Config::get('cashew::tables.subscriptions'))->insertGetId(array(
			'user_id' => $userId,
			'customer_id' => $customer->id(),
			'subscription_id' => $customer->subscription()->id(),
			'trial_ends_at' => $customer->subscription()->trialEnd(),
			'plan' => $customer->subscription()->plan(),
			'quantity' => $customer->subscription()->quantity(),
			'last_four' => $customer->card()->lastFour(),
			'status' => $customer->subscription()->status(),
			'created_at' => DB::raw('now()'),
			'updated_at' => DB::raw('now()')
		));

		return $id;
	}

	/**
	 * Updates the subscription by user
	 * @param  string   $userId
	 * @param  Customer $customer
	 * @return void
	 */
	public function customer($userId, Customer $customer)
	{
		DB::table(Config::get('cashew::tables.subscriptions'))
			->where('user_id', '=', $userId)
			->update(array(
				'customer_id' => $customer->id(),
				'last_four' => $customer->card()->lastFour(),
				'updated_at' => DB::raw('now()')
			));
	}

	/**
	 * Updates the subscription by subscription
	 * @param  string       $userId
	 * @param  Subscription $subscription
	 * @return void
	 */
	public function subscribe($userId, Subscription $subscription)
	{
		DB::table(Config::get('cashew::tables.subscriptions'))
			->where('user_id', '=', $userId)
			->update(array(
				'subscription_id' => $subscription->id(),
				'trial_ends_at' => $subscription->trialEnd(),
				'subscription_ends_at' => null,
				'plan' => $subscription->plan(),
				'quantity' => $subscription->quantity(),
				'status' => $subscription->status(),
				'updated_at' => DB::raw('now()'),
				'subscribed_at' => DB::raw('now()')
			));
	}

	/**
	 * Updates a subscription
	 * @param  string   $userId
	 * @param  Customer $customer
	 * @return integer
	 */
	public function update($userId, Customer $customer)
	{
		$subscription = $customer->subscription();

		$id = DB::table(Config::get('cashew::tables.subscriptions'))
			->where('user_id', '=', $userId)
			->update(array(
				'subscription_id' => $subscription->id(),
				'trial_ends_at' => $subscription->trialEnd(),
				'subscription_ends_at' => null, // null because update should never be used to stop the subscription
				'plan' => $subscription->plan(),
				'quantity' => $subscription->quantity(),
				'last_four' => $customer->card()->lastFour(),
				'status' => $subscription->status(),
				'updated_at' => DB::raw('now()')
			));

		return $id;
	}

	/**
	 * Updates the status of subscription
	 * @param  string $userId
	 * @param  string $status
	 * @return void
	 */
	public function updateStatus($userId, $status)
	{
		DB::table(Config::get('cashew::tables.subscriptions'))
			->where('user_id', '=', $userId)
			->update(array(
				'status' => $status,
				'updated_at' => DB::raw('now()')
			));
	}

	/**
	 * Resumes a subscription
	 * @param  string $userId
	 * @return integer
	 */
	public function resume($userId)
	{
		$id = DB::table(Config::get('cashew::tables.subscriptions'))
			->where('user_id', '=', $userId)
			->update(array(
				'subscription_ends_at' => null,
				'canceled_at' => null,
			));

		return $id;
	}

	/**
	 * Cancels a subscription
	 * @param  string       $userId
	 * @param  Subscription $subscription
	 * @return integer
	 */
	public function cancel($userId, Subscription $subscription)
	{
		$id = DB::table(Config::get('cashew::tables.subscriptions'))
			->where('user_id', '=', $userId)
			->update(array(
				'subscription_ends_at' => $subscription->end(),
				'status' => 'canceled',
				'updated_at' => DB::raw('now()'),
				'canceled_at' => DB::raw('now()')
			));

		return $id;
	}

	/**
	 * Expires a subscription
	 * @param  string $userId
	 * @return integer
	 */
	public function expire($userId)
	{
		$id = DB::table(Config::get('cashew::tables.subscriptions'))
			->where('user_id', '=', $userId)
			->update(array(
				'status' => 'expired',
				'updated_at' => DB::raw('now()'),
				'expired_at' => DB::raw('now()')
			));

		return $id;
	}

	/**
	 * Stores an invoice
	 * @param  string  $userId
	 * @param  Invoice $invoice
	 * @return integer
	 */
	public function storeInvoice($userId, Invoice $invoice)
	{
		$id = DB::table(Config::get('cashew::tables.invoices'))
			->insertGetId(array(
				'user_id' => $userId,
				'customer_id' => $invoice->customerId(),
				'subscription_id' => $invoice->subscriptionId(),
				'invoice_id' => $invoice->id(),
				'currency' => $invoice->currency(),
				'date' => Carbon::createFromTimestamp($invoice->date(false))->toDateTimeString(),
				'period_start' => Carbon::createFromTimestamp($invoice->periodStart(false))->toDateTimeString(),
				'period_end' => Carbon::createFromTimestamp($invoice->periodEnd(false))->toDateTimeString(),
				'total' => $invoice->total(),
				'subtotal' => $invoice->subtotal(),
				'discount' => $invoice->discount(),
				'created_at' => DB::raw('now()'),
				'updated_at' => DB::raw('now()')
			));

		return $id;
	}

	/**
	 * Returns the invoices
	 * @param  string  $userId
	 * @param  integer $count
	 * @return array
	 */
	public function getInvoices($userId, $count = 10)
	{
		$invoices = DB::table(Config::get('cashew::tables.invoices'))
			->where('user_id', $userId)
			->take($count)
			->orderBy('created_at', 'DESC')
			->get();

		foreach($invoices as $index => $invoice)
		{
			$invoices[$index] = new LocalInvoice($invoice);
		}

		return $invoices;
	}

	/**
	 * Returns the subscription by user
	 * @param  string $userId
	 * @return array
	 */
	private function subscriptionByUser($userId)
	{
		return DB::table(Config::get('cashew::tables.subscriptions'))
			->where('user_id', '=', $userId)
			->first();
	}

	/**
	 * Returns the subscription by customer
	 * @param  string $customerId
	 * @return array
	 */
	private function subscriptionByCustomer($customerId)
	{
		return DB::table(Config::get('cashew::tables.subscriptions'))
			->where('customer_id', '=', $customerId)
			->first();
	}
}
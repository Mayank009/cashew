<?php namespace Owlgrin\Cashew\Invoice;

use Owlgrin\Cashew\Invoice\Invoice;
use Carbon\Carbon;

/**
 * The local implementation of invoice
 */
class LocalInvoice implements Invoice {

	/**
	 * The raw invoice object
	 * @var array
	 */
	protected $invoice;

	public function __construct($invoice)
	{
		$this->invoice = $invoice;
	}

	/**
	 * Returns the identifier
	 * @return string
	 */
	public function id()
	{
		return $this->invoice['invoice_id'];
	}

	/**
	 * Returns the customer id
	 * @return string
	 */
	public function customerId()
	{
		return $this->invoice['customer_id'];
	}

	/**
	 * Returns the subscription if
	 * @return string
	 */
	public function subscriptionId()
	{
		return $this->invoice['subscription_id'];
	}

	/**
	 * Returns the currency
	 * @return string
	 */
	public function currency()
	{
		return $this->invoice['currency'];
	}

	/**
	 * Returns the date of invoice
	 * @param  boolean $formatted
	 * @return integer|string
	 */
	public function date($formatted = true)
	{
		return $formatted
			? Carbon::createFromFormat('Y-m-d H:i:s', $this->invoice['date'])->toFormattedDateString()
			: Carbon::createFromFormat('Y-m-d H:i:s', $this->invoice['date'])->getTimestamp();
	}

	/**
	 * Returns the start of period of invoice
	 * @param  boolean $formatted
	 * @return integer|string
	 */
	public function periodStart($formatted = true)
	{
		return $formatted
			? Carbon::createFromFormat('Y-m-d H:i:s', $this->invoice['period_start'])->toFormattedDateString()
			: Carbon::createFromFormat('Y-m-d H:i:s', $this->invoice['period_start'])->getTimestamp();
	}

	/**
	 * Returns the end of period of invoice
	 * @param  boolean $formatted
	 * @return integer|string
	 */
	public function periodEnd($formatted = true)
	{
		return $formatted
			? Carbon::createFromFormat('Y-m-d H:i:s', $this->invoice['period_end'])->toFormattedDateString()
			: Carbon::createFromFormat('Y-m-d H:i:s', $this->invoice['period_end'])->getTimestamp();
	}

	/**
	 * Returns the total of invoice
	 * @return number
	 */
	public function total()
	{
		return $this->invoice['total'];
	}

	/**
	 * Returns the formatted total of invoice
	 * @return string
	 */
	public function formattedTotal()
	{
		return $this->_formatted($this->total());
	}

	/**
	 * Returns the subtotal of invoice
	 * @return number
	 */
	public function subtotal()
	{
		return $this->invoice['subtotal'];
	}

	/**
	 * Returns the formatted subtotal
	 * @return string
	 */
	public function formattedSubtotal()
	{
		return $this->_formatted($this->subtotal());
	}

	/**
	 * Tells if invoice has discount or not
	 * @return boolean
	 */
	public function hasDiscount()
	{
		return $this->invoice['total'] > 0 and $this->invoice['subtotal'] != $this->invoice['total'];
	}

	/**
	 * Returns the discount in invoice
	 * @return number
	 */
	public function discount()
	{
		return $this->subtotal() - $this->total();
	}

	/**
	 * Returns the formatted discount in invoice
	 * @return string
	 */
	public function formattedDiscount()
	{
		return $this->_formatted($this->discount());
	}

	/**
	 * Formats the number
	 * @param  number $amount
	 * @return string
	 */
	private function _formatted($amount)
	{
		return number_format(round(money_format('%i', $amount), 2), 2);
	}
}
<?php

	require_once(dirname(__DIR__, 2) . '/database.php');
    require_once(dirname(__DIR__, 2) . '/functions.php');

    checkaccess(basename(__FILE__));
	$title = 'Manage Customers';

    //Get list of customers
    $customers = $stripeClient->customers->all();
    $customerList = [];

    if(!empty($customers->data)) {
        foreach($customers->data as $customer) {
            $customer->registered = stripeIsRegistered($customer->id);
            array_push($customerList, $customer);
        }
    }

    require_once(dirname(__DIR__, 3) . '/admin/includes/header.php');
    echo stripeModeBanner();
?>

<?php if(isset($_GET['id'])) : ?>
    <?php
        //Redirect to list if customer does not exist
        try {
            $customer = $stripeClient->customers->retrieve($_GET['id']);
        }
        catch(Exception $e) {
            http_response_code(404);
            header('Location: ' . explode('?', $_SERVER['REQUEST_URI'])[0]);
            exit();
        }

        //Get subscriptions
        try {
            $subscriptions = $stripeClient->subscriptions->all([
                'customer' => $customer->id
            ]);

            $si = 1;

            function statuscolour($status) {
                switch($status) {
                    case 'past_due':
                    case 'unpaid':
                    case 'canceled':
                        $colour = 'danger';
                        break;
                    case 'incomplete':
                    case 'incomplete_expired':
                    case 'ended':
                        $colour = 'warning';
                        break;
                    case 'active':
                        $colour = 'success';
                        break;
                    default:
                        $colour = 'info';
                        break;
                }

                return $colour;
            }
        }
        catch(Exception $e) {

        }

        //Get payments

        //Get invoices
    ?>

    <div class="col-lg-3 bg-light py-3">
        <div class="form-group mb-3">
            <input type="button" class="btn btn-dark mb-1" name="returnList" value="Return to List">
            <?php echo stripeExternalLink('customers/' . $_GET['id'], 'Manage Customer'); ?>
        </div>

        <form id="customerGeneral" method="post">
            <input type="hidden" name="id" value="<?php echo $customer->id; ?>">

            <div class="sectionHeading">
                <h5>General Details</h5>
                <button type="button" class="btn btn-link link-dark" aria-expanded="true" data-bs-toggle="collapse" data-bs-target="#general"><span class="fa fa-chevron-left"></span></button>
            </div>

            <div class="section collapse show" id="general">
                <div class="sectionInner">
                    <div class="form-group mb-3">
                        <label class="required">Customer Name</label>
                        <input type="text" class="form-control" name="customerName" value="<?php echo $customer->name; ?>" required>
                    </div>

                    <div class="form-group mb-3">
                        <label class="required">Description</label>
                        <textarea class="form-control countChars" name="customerDescription" maxlength="500"><?php echo $customer->description; ?></textarea>
                    </div>

                    <div class="form-group mb-3">
                        <label class="required">Email</label>
                        <input type="email" class="form-control" name="customerEmail" value="<?php echo $customer->email; ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="required">Phone</label>
                        <input type="text" class="form-control" name="customerPhone" value="<?php echo $customer->phone; ?>">
                    </div>
                </div>
            </div>

            <hr>

            <div class="sectionHeading">
                <h5>Billing Address</h5>
                <button type="button" class="btn btn-link link-dark" aria-expanded="false" data-bs-toggle="collapse" data-bs-target="#billing"><span class="fa fa-chevron-left"></span></button>
            </div>

            <div class="section collapse" id="billing">
                <div class="sectionInner">
                    <div class="form-group mb-3">
                        <label>Line 1</label>
                        <input type="text" class="form-control" name="billingLine1" value="<?php echo $customer->address->line1; ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label>Line 2</label>
                        <input type="text" class="form-control" name="billingLine2" value="<?php echo $customer->address->line2; ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label>City</label>
                        <input type="text" class="form-control" name="billingCity" value="<?php echo $customer->address->city; ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label>County</label>
                        <input type="text" class="form-control" name="billingState" value="<?php echo $customer->address->state; ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label>Postcode</label>
                        <input type="text" class="form-control" name="billingPostcode" value="<?php echo $customer->address->postal_code; ?>">
                    </div>

                    <div class="form-group">
                        <label>Country</label>
                        <select class="form-control" name="billingCountry">
                            <option selected>-- Select Country --</option>

                            <?php foreach(STRIPE_COUNTRIES as $iso => $country) {
                                echo '<option value="' . $iso . '"' . ($iso === $customer->address->country ? ' selected' : '') . '>' . $country . '</option>';
                            } ?>
                        </select>
                    </div>
                </div>
            </div>

            <hr>

            <div class="sectionHeading">
                <h5>Shipping Address</h5>
                <button type="button" class="btn btn-link link-dark" aria-expanded="false" data-bs-toggle="collapse" data-bs-target="#shipping"><span class="fa fa-chevron-left"></span></button>
            </div>

            <div class="section collapse" id="shipping">
                <div class="sectionInner">                
                    <p class="text-muted"><small>Leave blank if same as billing address.</small></p>

                    <div class="form-group mb-3">
                        <label>Line 1</label>
                        <input type="text" class="form-control" name="shipingLine1" value="<?php echo $customer->shipping->address->line1; ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label>Line 2</label>
                        <input type="text" class="form-control" name="shipingLine2" value="<?php echo $customer->shipping->address->line2; ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label>City</label>
                        <input type="text" class="form-control" name="shipingCity" value="<?php echo $customer->shipping->address->city; ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label>County</label>
                        <input type="text" class="form-control" name="shipingState" value="<?php echo $customer->shipping->address->state; ?>">
                    </div>

                    <div class="form-group mb-3">
                        <label>Postcode</label>
                        <input type="text" class="form-control" name="shipingPostcode" value="<?php echo $customer->shipping->address->postal_code; ?>">
                    </div>

                    <div class="form-group">
                        <label>Country</label>
                        <select class="form-control" name="shippingingCountry">
                            <option selected>-- Select Country --</option>

                            <?php foreach(STRIPE_COUNTRIES as $iso => $country) {
                                echo '<option value="' . $iso . '"' . ($iso === $customer->shipping->address->country ? ' selected' : '') . '>' . $country . '</option>';
                            } ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group mt-3">
                <input type="submit" class="btn btn-primary" name="updateCustomer" value="Update Customer">
            </div>
        </form>
    </div>

    <div class="col py-3">
        <div class="sectionHeading">
            <h5>Payment Methods</h5>
            <button type="button" class="btn btn-link link-dark" aria-expanded="true" data-bs-toggle="collapse" data-bs-target="#methods"><span class="fa fa-chevron-left"></span></button>
        </div>

        <div class="section collapse show" id="methods">
            <div class="sectionInner">
                <?php
                    //Get list of payment cards
                    try {
                        $paymentCards = $stripeClient->customers->allPaymentMethods($customer->id, ['type' => 'card']);
                    }
                    catch(Exception $e) {
                        echo '<div class="alert alert-info">' . $e . '</div>';
                    }

                    //Get list of direct debits (SEPA)
                    try {
                        $paymentDebits = $stripeClient->customers->allPaymentMethods($customer->id, ['type' => 'sepa_debit']);
                    }
                    catch(Exception $e) {
                        echo '<div class="alert alert-info">' . $e . '</div>';
                    }
                ?>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Type</th>
                                <th class="shorten">Card Number</th>
                                <th class="shorten text-center">Expiry</th>
                                <th class="shorten text-center"></th>
                                <th class="shorten">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if(!empty($paymentCards->data)) : ?>
                                <?php foreach($paymentCards->data as $card) : ?>
                                    <tr>
                                        <td>
                                            <span class="fab fa-cc-<?php echo $card->card->brand; ?> me-2"></span><span><?php echo ucwords($card->card->brand . ' ' . $card->card->funding); ?></span>
                                        </td>

                                        <td class="shorten">
                                            <span>**** **** **** <?php echo $card->card->last4; ?></span>
                                        </td>

                                        <td class="shorten text-center">
                                            <span><?php echo sprintf("%02d", $card->card->exp_month) . '/' . $card->card->exp_year; ?></span>
                                        </td>

                                        <td class="shorten text-center">
                                            <?php 
                                                echo ($card->id === $customer->invoice_settings->default_payment_method || $card->id === $customer->default_source ? '<span class="alert alert-info rounded px-2 py-1 m-0">default</span>' : ''); 
                                                echo (time() >= strtotime($card->card->exp_year . '-' . sprintf("%02d", $card->card->exp_month) . '-01') ? '<span class="alert alert-danger rounded px-2 py-1 m-0">expired</span>' : '')
                                            ?>                                            
                                        </td>

                                        <td class="shorten">
                                            <div class="d-flex align-items-center mb-n1">
                                                <?php if($card->id !== $customer->invoice_settings->default_payment_method && $card->id !== $customer->default_source) : ?>
                                                    <form id="removeMethod" mehtod="post">
                                                        <input type="submit" class="btn btn-danger mb-1 me-1" name="removeMethod" value="Remove">
                                                    </form>

                                                    <form id="defaultMethod" method="post">
                                                        <input type="submit" class="btn btn-secondary mb-1" name="setDefault" value="Set Default">
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                    <tr>
                                        <td colspan="5"><div class="alert alert-info mb-0 px-2 py-1">No payment cards exist</div></td>
                                    </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <hr>

        <div class="sectionHeading">
            <h5>Subscriptions</h5>
            <button type="button" class="btn btn-link link-dark" aria-expanded="false" data-bs-toggle="collapse" data-bs-target="#subscriptions"><span class="fa fa-chevron-left"></span></button>
        </div>

        <div class="section collapse" id="subscriptions">
            <div class="sectionInner">
                <?php if(!empty($subscriptions->data)) : ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                    <th class="shorten text-center">#</th>
                                    <th>Product</th>
                                    <th class="shorten text-center">Start Date</th>
                                    <th class="shorten text-center">Next Billing Date</th>
                                    <th class="shorten text-center">Status</th>
                                    <th class="shorten">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach($subscriptions->data as $subscription) : ?>
                                    <tr>
                                        <td><span><?php echo $si; $si++; ?></span></td>

                                        <td>
                                            <?php foreach($subscription->items->data as $lineItem) : ?>
                                                <span><?php echo $stripeClient->products->retrieve($lineItem->price->product)->name; ?></span>
                                                <span class="mx-3">|</span>
                                                <small><?php echo number_format($lineItem->price->unit_amount / 100, 2) . ' (' . $lineItem->price->currency . ') ' . $lineItem->price->recurring->interval . 'ly'; ?></small><br>
                                            <?php endforeach; ?>
                                        </td>

                                        <td class="shorten text-center">
                                            <span><?php echo date('d/m/Y', $subscription->start_date); ?></span>
                                        </td>

                                        <td class="shorten text-center">
                                            <span><?php echo date('d/m/Y', $subscription->current_period_end); ?></span>
                                        </td>

                                        <td>
                                            <span class="alert alert-<?php echo statuscolour($subscription->status); ?> rounded px-2 py-1 m-0"><?php echo $subscription->status; ?></span>
                                        </td>

                                        <td><?php echo stripeExternalLink('subscriptions/' . $subscription->id, 'Manage'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <hr>

        <div class="sectionHeading">
            <h5>Payments</h5>
            <button type="button" class="btn btn-link link-dark" aria-expanded="false" data-bs-toggle="collapse" data-bs-target="#payments"><span class="fa fa-chevron-left"></span></button>
        </div>

        <div class="section collapse" id="payments">
            <div class="sectionInner">
                
            </div>
        </div>

        <hr>

        <div class="sectionHeading">
            <h5>Invoices</h5>
            <button type="button" class="btn btn-link link-dark" aria-expanded="false" data-bs-toggle="collapse" data-bs-target="#invoices"><span class="fa fa-chevron-left"></span></button>
        </div>

        <div class="section collapse" id="invoices">
            <div class="sectionInner">
                
            </div>
        </div>
    </div>
<?php else : ?>
    <div class="col-lg-3 bg-light py-3">
		<h3>Search Customers</h3>
		
		<form id="searchCustomers">
			<div class="form-group">
				<div class="input-group">
					<input type="text" class="form-control" name="search" placeholder="Search by name or email..." value="<?php echo $_GET['search']; ?>" required>
					
					<input type="submit" class="btn btn-primary" value="Search">
					
					<?php if(isset($_GET['search'])) : ?>
						<input type="button" class="btn btn-dark" name="clearSearch" value="Clear">
					<?php endif; ?>
				</div>
			</div>
		</form>

        <hr>

        <?php echo stripeExternalLink('customers', 'Manage All Customers'); ?>
	</div>

	<div class="col py-3">
		<h3>Current Customers</h3>

        <?php
            //Filter the array based on customer name, email matching the searched value
            if(isset($_GET['search'])) {
                $search = $_GET['search'];
                
                $customerList = array_filter($customerList, function($value) use ($search) {
                    if(stripos($value['name'], $search) !== false || stripos($value['email'], $search) !== false) {
                        return true;
                    }
                    else {
                        return false;
                    }
                });
            }

            //Paginate the array
            $pagination = new pagination(count($customerList));
            $pagination->load();

            $customerList = array_slice($customerList, $pagination->offset, $pagination->itemsPerPage);
        ?>

        <?php if(!empty($customerList)) : ?>
            <div class="table-responsive">
				<table class="table table-striped">
					<thead class="table-dark">
						<tr>
							<th class="shorten">ID</th>
							<th>Details</th>
							<th class="shorten">Date Created</th>
							<th class="shorten">Status</th>
							<th class="shorten">Actions</th>
						</tr>
					</thead>
					
					<tbody>
						<?php foreach($customerList as $customer) : ?>
							<tr>
								<th class="shorten" scope="row"><?php echo $customer->id; ?></th>
								
								<td>
									<span><strong><?php echo $customer->name; ?></strong></span><br>
                                    
                                    <?php if(!empty($customer->email)) : ?>
									    <small>Email: <a href="mailto:<?php echo $customer->email; ?>"><?php echo $customer->email; ?></a></small><br>
                                    <?php endif; ?>

                                    <?php if(!empty($customer->phone)) : ?>
									    <small>Phone: <a href="tel:<?php echo $customer->phone; ?>"><?php echo $customer->phone; ?></a></small><br>
                                    <?php endif; ?>
								</td>
								
								<td class="shorten">
									<?php echo date('d/m/Y', $customer->created); ?><br>
									<?php echo date('H:i', $customer->created); ?>
								</td>

                                <td class="shorten text-center">
                                    <span><?php echo (stripeIsRegistered($customer->id) ? 'Registered' : 'Guest'); ?></span>
                                </td>
								
								<td class="shorten">
									<div class="form-group mb-n1">
										<?php echo editbutton($customer->id); ?>
									</div>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

            <?php echo $pagination->display(); ?>
        <?php else : ?>
            <div class="alert alert-info">No customers could be found</div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once(dirname(__DIR__, 3) . '/admin/includes/footer.php'); ?>
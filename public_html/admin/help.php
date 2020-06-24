<? 
if(basename($_SERVER['PHP_SELF'])!="admin.php"){header("Location: ../admin.php");}//direct access security 

$pg=isset($_GET['pg'])?$_GET['pg']:"";
$helptitles=array(
"dept"=>"Department Builder",
"product"=>"Product Builder",
"attach"=>"Attaching Product Options",
"packages"=>"Product Packages",
"product_options"=>"Product Options",
"invoices"=>"Invoices",
"customers"=>"Customers",
"enquiries"=>"Enquiries",
"promotions"=>"Site Promotions",
"reports"=>"Reports",
"admins"=>"Admin Logon"
);
?>

<div id="bread">You are here: <a href="<?=$mainbase?>">Admin Home</a> &#187; Help Files</div>
<div id="main">
	<h2 id="pagetitle">Help File:
		<?=$helptitles[$pg]?>
	</h2>
	<div id="pagecontent">
		<div id="errorbox" style="<?=$errorboxdisplay?>">
			<p>Error</p>
			<?=$errormsg?>
		</div>
		<!-- CONTENT -->
		<? 
		switch($pg)
		{
			case "dept":
				?>
				<p>The Department Builder is where you set up your department structure for your site..</p>
				<ul id="help">
					<li><strong>Title</strong> The name or title of the department or sub department.</li>
					<li><strong>Main image</strong> - Shown on the home page or on a sub level</li>
					<br />
					<li><strong>On / Off</strong> - Check this box to have the department displayed on the site.</li>
					<li><strong>Show in side menu</strong> - Check this box to have the department displayed in the site's left menu.</li>
					<li><strong>Show on home page</strong> - Check this box to have the department displayed on the home page.</li>
					<li><strong>Long Description</strong> - If using descriptions on the site, this is a more comprehensive description of the department.</li>
				</ul>
				<?
				break;
			case "product":
				?>
				<p>The Product Builder is where you set up your products so that they are ready to sell in your online shop.
				<ul id="help">
					<li><strong>Standard Options</strong> - If this product has options and those options have already been created in the "Option Builder" click this link to attach desired options to the product.</li>
					<br />
					<li><strong>Title</strong> - The name or title of the product.</li>
					<li><strong>Product Code</strong> - The unique item number or product code.</li>
					<li><strong>Price</strong> - The price at which you are selling this product.</li>
					<li><strong>R.R.P.</strong> - The recommended retail price at which the item was sold for pricing.</li>
					<li><strong>Exclude from discounts</strong> - Check this box to prevent discounts being applied to this product.</li>
					<br />
					<li><strong>Item Weight</strong> - The weight of the item, used to calculate postage by weight.
					<li><strong>Postage Notes</strong> - A short line of text to describe the postage details. For example, "Dispatched in 2-3 Days" or "Sent Immediately". This gives your customers more information about the product and it's availability.</li>
					<li><strong>Free Postage</strong> - If checked, this product has no associated postage costs.</li>
					<br />
					<li><strong>Small, Medium &amp; Large image</strong> - Three possible images for the product, normally used as (Small)Thumbnail, (Medium)Product Page and (Large)Large Detail Image.</li>
					<br />
					<li><strong>File Name (URL)</strong> - The corresponding url friendly title for this product.</li>
					<li><strong>Meta Description</strong> - Used "behind the scenes" for search engines. This should be a keyword heavy description relative to the product's content and description.</li>
					<br />
					<li><strong>On / Off</strong> - Check this box to have the product turn live on the site.</li>
					<li><strong>VAT / Tax</strong> - Check this box to have the current VAT / Tax settings applied to this product.</li>
					<li><strong>On Sale</strong> - If checked, the system will note that this product has a sale price.</li>
					<li><strong>Short Description</strong> - If using descriptions on the site, this is a brief description of the product.</li>
					<li><strong>Long Description</strong> - If using descriptions on the site, this is a more comprehensive description of the product.</li>
				</ul>
				<?
				break;
			case "attach":
				?>
				<p>Product options aallow customers to select a colour or size option for their purchase.</p>
				<p>The editor will give you a list of possible choices available, just choose the one you want to add click the &#62;&#62; and it will transfer the choice to that product.  In the right hand window, select the choice you want to remove and click the &#60;&#60; to remove it from the product.</p>
				<?
				break;
			case "packages":
				?>
				<p>Product packages allow you to build, view and edit product kits within the Admin CP.</p>
				<p>Product packages are combinations of individual products grouped under one product. To create a package, you must first create the product that will be the main package. Create this package just as you would a single product.</p>
				<p>Next load up the Product package builder and choose "Create New package".  This will take you to a listing of all available departments - select the department that contains your original product, and click on the "View Items". In the right hand window, you will see a list of products availble in that directory. Select the product you are wanting to include in the package, and click the "Build package" button.</p>
				<p>In the left hand window, select the department that has one or more of the products you wish to include in the package, click "View Items" and select the appropriate items to include in your product package.</p>
				<p>Once you have added all the products, the package will be assembled.</p>
				<p>Products included in package can have standard options, but cannot have custom ones.</p>
				<?
				break;
			case "product_options":
				?>
				<p> The option editor allows you to create dropdown lists for your product pages.  Options like Shirt Size: Small/Medium/Large, Colour: Red/Blue/Green </p>
				<p> Each option item can be tracked with stock control.  Once the option item reaches 0, it will be turned off and not available. </p>
				<p>Options can also have an additional price, if you provide an additional price, it will be calculated by multiplying the quantity * additional price and also an image.</p>
				<?
				break;
			case "invoices":
				?>
				<p>The order manager allows you to view orders, sort orders and search for orders.</p>
				<p><strong>View Order List</strong></p>
				<p>This list allows you to see an overview of current orders.
					<ul id="help">
						<li><strong>Pay Status</strong> Either marked Paid or UnPaid</li>
						<br />
						<li><strong>Status</strong> What action (if any) has been taken with this product.
							<ul>
								<li>New - Order is a new order</li>
								<li>Backordered - Set to backorder if order is not ready for posting</li>
								<li>Dispatched - If order has been posted</li>
								<li>Void - Like delete but keeps the order in system</li>
								<li>Delete - Totally removes the order from the system.  (if using stock control, this will affect stock levels)</li>
							</ul>
						</li>
					</ul>
				</p>
				<p><strong>View Order Details</strong></p>
				<p>Viewing an order lets you view the order.  It allows you to make modifications to the products, add quantities, remove items, change options.</p>
				<?
				break;
			case "customers":
				?>
				This module allows you to completely manage your customers and view their order history.
				<br />
				<br />
				<strong>Add New Customer</strong>
				<br />
				Click the "Add New Customer" link to create a new customer in your database.
				<br />
				<br />
				<strong>Customer Details</strong>
				<br />
				By clicking the customer details, you can view the individual customers information.  Also listed is the customers order history.  You can view each individual order by clicking the 'details' link on the invoice of your choice.
				<?
				break;
			case "enquiries":
				?>
				The enquiries are populated by customers who visit the site and fill out the contact form. Their details are put into the Manage Contacts list, where you can view their name, address, contact details and request.
				<?
				break;
			case "promotions":
				?>
				<p>Site Promotions are very powerful methods of increasing your orders and order sizes. If a discount code is provided on the shopping cart page, the cart will take the percentage off of the total order and modify the final cost.
				<ul>
				<li>Each promotion is based on dates for its "Live" period.</li>
				<li>You can have all the promotions turning live at the same time.</li>
				<li>Customers will need to enter the discount code to apply it to their order.</li>
				</ul>
				</p>
				
				<p><strong>Discount code</strong> This is the code which customers will use to activate the discount.</p>
				<p><strong>Discount value</strong> The discount total will be calculated using this percentage</p>
				<p><strong>Start date</strong> This is the first day this promotion will become valid.</p>
				<p><strong>End date</strong> The promotion will end on this date.</p>
				<p><strong>On/Off</strong> You can use this setting to turn this promotion off which will prevent it from being used. In addition to this set the end date into the past (ie before today).  This will doubly insure that the promotion will not be valid.</p>
				<p><strong>Use products list</strong> Use this setting to switch from a global discount or discount only the products added to the list. Products excluded from discounts (set via the shop builder product edit page) will not be discounted in either case. If this is selected and the list is empty, the discount will default to a global discount.</p>
				<h3>The product list</h3>
				<strong>From the left side list:</strong><br />
				Choose a department and click &quot;View Items&quot;. Only departments set to be displayed (via shop builder) and with atleast one non discount-excluded product will be shown. The list will now display products from within the chosen department.<br />
				Click a product or use ctrl+click to select multiple products and click &quot;Add&quot; to add the selected product(s) to the right hand &quot;Selected products list&quot; or click &quot;Back&quot; to return to the departments list.<br /><br />
				<strong>From the right side list</strong><br />
				Click a product or use ctrl+click to select multiple products and click &quot;Remove from list&quot; to remove the selected product(s) from the right hand &quot;Selected products list&quot;.
				<?
				break;
			case "reports":
				?>
				<p><strong>Order reports</strong> - Allows you to view total sales based on specific periods.</p>
				<p><strong>Product report</strong> - Shows all product pages within your shop.</p>
				<p><strong>Stock report</strong> - View current stock levels for all products</p>
				<p><strong>Today's orders</strong> - Allows you to view the total sales for todays date only.</p>
				<?
				break;
			case "admins":
				?>
				Add, Edit and Delete users who will be using the Admin CP.
				<br /><br />
				You have the ability to grant users various levels of access to different parts of the admin. If they do not have access, they do not see any links to the various modules. Simply use the check boxes to set up their access priveliges.
				<?
				break;
			case "postage":
				?>
				<h3>Default Postage options</h3>
				
				<p><strong>For a fixed postage cost:</strong>
				<br />
				Add a new row to the postal ranges with &#163;0 starting and &#163;0 end value put the fixed charge in the field under the &quot;Value&quot; column.</p>
				<p><strong>Ranged postage costs:</strong>
				<br />
				Set &quot;Range start&quot; to the order value which will trigger this range, set &quot;Range end&quot; to the order value which will dable this range for the customers' order and finally set the &quot;Value&quot; to be the postage charge for this range.</p>
				<h3>Special Rates (offered in addition to the default options)</h3>
				You can set alternate postage options here which can be chosen as an improved postage service. ie: first class or international rates.
				<p><strong>Using time constraints:</strong>
				<br />
				You can set a time limit to when the postage options will be available. If you set the day to Friday for example, orders must be placed before Friday 12am in order for the option to be selectable. If you set only the time, the option is available before the specified time every day. Setting both day and time will only allow the option to be chosen if ordering before that setting (ie if set to Friday 12pm, it will be unavailable to select on Friday 1pm onwards until Saturday).</p>
				<?
				break;
		}
		?>
		<!-- /CONTENT -->
	</div>
</div>


# commerce other services (important: under construction)
commerce other services for Commerce 2.x

This module is based on the "donation example" module by bojanz.
You can find it [here](https://github.com/bojanz/donation_example)
[https://github.com/bojanz/donation_example]

Order item type
---------------
This module creates "other services" order item type.

Forms
-----
Based on the initial logic behind creating this module, there are two forms:
1- Dashboard form
This is where moderator/admin sets the price of the 'other' order item. The information are saved in the database in the custom table: 'commerce_other_services' which was created upon the install of this module.
See: commerce_other_services.install

2- Customer form (block)
This is where the customer view the information about the price of the "other service." All fields are disabled because they were set in advance by the moderator/admin.
The customer has to review the information and hit 'add to cart' button.


How it works
-----

Find the clunkiest computer in house, log in to github, clone and enable this module. (I don't guarantee that your computer would work from this point onward)
go to : /admin/structure/block
and place the block called "Other Services" any where you want.

The moderator/admin form can only be accessed through the route mentioned in .routing.yml

I am still working on hiding the block if "processed" = 0
Wish me luck!

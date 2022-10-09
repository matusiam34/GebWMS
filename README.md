# GebWMS
A very basic yet functional tool that can act as a warehouse management system or be just something you integrate with systems that you already own that do not have inventory capabilities.

The code currently looks like it has been to war because I put it together from few different projects... Over time it will get cleaned up (hopefully). At this stage it is not really production ready since many parts have not been moved over yet. I just needed it to be out here to keep me motivated. Otherwise nothing will ever be completed.

Few features the system currently supports:
- many warehouses,
- flexibile locations (one to one, many to one, many mixed to one),
- products can be in eaches, cases and pallets,
- basic access control (admin, manager, supervisor, operator),
- it will work on a phone, tablet and computer (responsive design).

The goal is to make it usable which means that it will need the following features:
- bin to bin,
- goods in,
- picking lists.

Everything else will be a bonus.

I am not using any MVC framework here as I am not sure if it is worth adding such a massive overhead for a tiny system like this. Unless you have some kind of lightweight recommendation. For my CSS needs I am using Bulma since it is easy to use and tiny.

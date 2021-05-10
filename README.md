# mmsm-ordersapi
Repository for orders API and orders database


Markup : - [ ] GET     :: /api/orders/[id]   =>  will return json representation of order
: - [x] POST    :: /api/orders/       =>  will save order carried as json in body, as a new order, replies with OrderID if creation is successfull.
: - [ ] PUT     :: /api/orders/       =>  will update the specified order, with the present canges, from json carried in body
: - [ ] DELETE  :: /api/orders/[id]   =>  will delete the specified order
: - [ ] HEAD    :: /api/orders/[id]   =>  will return size information on data for specified order
: - [ ] OPTIONS :: /api/orders/       =>  will return allowed REST methods

Er det nødvendigt at opdatere en ordre, er de ikke Final?? altså afgivne og ikke til at ændre?
# mmsm-ordersapi
Repository for orders API and orders database


* GET     :: /api/orders/[id]     => will return json representation of order
* GET     :: /api/orders/last/    => will return the last order created
* GET     :: /api/orders/last/[N] => will return the last 'N' orders created
* POST    :: /api/orders/         => will save order carried as json in body, as a new order, replies with OrderID if creation is successfull.
* PUT     :: /api/orders/         => will update the specified order, with the present canges, from json carried in body
* DELETE  :: /api/orders/[id]     => will delete the specified order
* HEAD    :: /api/orders/[id]     => will return size information on data for specified order
* OPTIONS :: /api/orders/         => will return allowed REST methods
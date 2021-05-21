# mmsm-ordersapi
Repository for orders API and orders database

## Endpoints

- [x] GET     :: /api/orders/{orderId}              => Returns JSON representation of order
- [x] GET     :: /api/orders/{locationId}/          => Takes QueryParams (sortBy, page, size) Returns all location orders sorted, on pages, with given size
- [x] GET     :: /api/orders/{locationId}/last/{n}  => Returns the last 'n' orders created for location 'locationId'
- [x] GET     :: /api/orders/{userId}/all           => Returns all orders for user
- [x] POST    :: /api/orders                        => Creates order carried as JSON, replies with OrderID upon success
- [x] PATCH   :: /api/orders/{orderId}              => Updates order, with changes carried as JSON
- [x] PATCH   :: /api/orders/delivered/{orderId}    => Updates orderitems marked as delivered, setting time of delivery
- [x] DELETE  :: /api/orders/{orderId}              => Deletes the specified order


#### POST Order example JSON object
```json
{
    "location": "FranDine Bellinge",
    "locationId": 23,
    "server": "Bernadette Harroldson",
    "items": [
        {
            "nr": 45,
            "name": "Rullekebab",
            "cost": 35
        },
        {
            "nr": 65,
            "name": "Faxe Kondi, 0,5L",
            "cost": 15
        }
    ],
    "discount": 20,
    "total": 40
}
```

#### PATCH Order example JSON object /api/orders/{60a2580b6eac0d0e0501ed5b}
#### updating order items on order
```json
{
    "items": [
        {
            "itemUUID": "60a2aea33b60283dc84b4eb2",
            "nr": 30,
            "name": "Cheseburger",
            "cost": 35
        }
    ]
}
```

#### PATCH Order example JSON object for /api/orders/delivered/{60a2580b6eac0d0e0501ed5b}
#### updating delivery status for order item
```json
{
    "items": [
        {
            "itemUUID": "60a2580b6eac0d0e0501ed5d35",
            "delivered": true
        }

    ]
}
```
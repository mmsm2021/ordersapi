# mmsm-ordersapi
Repository for orders API and orders database

## Endpoints

- [x] GET     :: /api/orders/{orderId}                          => Returns JSON representation of order
- [ ] GET     :: /api/orders/{locationId}/{sort}/{page}/{size}  => Returns all location orders sorted, on pages, with given size
- [x] GET     :: /api/orders/{locationId}/last/{n}              => Returns ID's of the last 'n' orders created
- [x] GET     :: /api/orders/{userId}/all                       => Returns all orders for user
- [x] POST    :: /api/orders                                    => Creates order carried as JSON, replies with OrderID upon success
- [x] PATCH   :: /api/orders/{orderId}                          => Updates order, with changes carried as JSON
- [x] DELETE  :: /api/orders/{orderId}                          => Deletes the specified order
- [ ] HEAD    :: /api/orders/{id}                               => Returns content-length
- [ ] OPTIONS :: /api/orders/                                   => Returns allowed REST methods for this endpoint


## POST Order example JSON object
```json
{
    "location": "FranDine Strøget",
    "locationId": 45,
    "server": "Bertram Nissen",
    "customer": "aperson@online.com",
    "items": [
        {
            "nr": 30,
            "name": "Cheeseburger",
            "cost": 35
        },
        {
            "nr": 80,
            "name": "Pommes Frites",
            "cost": 15
        }
    ],
    "discount": 20,
    "total": 40
}
```

## PUT Order example JSON object
```json
{
    "orderId": "6098fe8cc369136a2016f127",
    "server": "Jokum Jespersen",
    "items": [
        {
            "nr": 35,
            "name": "Weggie Burger",
            "cost": 45
        },
        {
            "nr": 15,
            "name": "Stor fadøl, Carls Classic",
            "cost": 25
        }
    ]
}
```
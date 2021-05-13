# mmsm-ordersapi
Repository for orders API and orders database

## Endpoints

- [x] GET     :: /api/orders/{id}     => Returns JSON representation of order
- [ ] GET     :: /api/orders/last/    => Returns ID of the last order created
- [ ] GET     :: /api/orders/last/[N] => Returns ID's of the last 'N' orders created
- [x] POST    :: /api/orders          => Creates order carried as JSON, replies with OrderID upon success
- [ ] PATCH   :: /api/orders/[id]     => Updates order, with changes carried as JSON
- [ ] DELETE  :: /api/orders/[id]     => Deletes the specified order
- [ ] HEAD    :: /api/orders/[id]     => Returns content-length
- [ ] OPTIONS :: /api/orders/         => Returns allowed REST methods for this endpoint


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
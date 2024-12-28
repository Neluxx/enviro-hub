
# enviro-hub-symfony

Symfony-based API for storing, processing, and alerting based on environmental sensor data from distributed nodes.


## Tech Stack

- PHP 8.2
- Symfony 7.2
- PicoCSS
- HTMX


## Installation

Clone the project

```bash
  git clone https://github.com/Neluxx/enviro-hub-symfony.git
```

Go to the project directory

```bash
  cd enviro-hub-symfony
```

Install dependencies

```bash
  composer install
```

Start the server with Symfony CLI

```bash
  symfony server:start
```


## API Reference

#### Submit environmental data to the hub

```http
  POST /api/data
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `temperature` | `float` | **Required**. The temperature in degrees Celsius. |
| `humidity` | `float` | **Required**. The relative humidity in percent. |
| `pressure` | `float` | **Required**. The atmospheric pressure in hPa. |
| `co2` | `float` | **Required**. The CO₂ level in ppm. |
| `created` | `datetime` | **Required**. The time of the measurement. |



## License

[Apache License 2.0](https://github.com/Neluxx/enviro-hub-symfony/blob/main/LICENSE)


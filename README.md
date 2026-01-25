
# enviro-hub

Symfony-based API for storing, processing, and alerting based on environmental sensor data from distributed nodes.

## Requirements

Ensure these tools are installed on your system:

- [Git](https://git-scm.com/downloads) (for version control and collaboration)
- [Make](https://wiki.ubuntuusers.de/Makefile/) (to run setup and dev tasks)
- [DDEV](https://ddev.readthedocs.io/en/stable/) (for local Symfony dev environment)

## Setup

1. Check out the [repository](https://github.com/Neluxx/enviro-hub.git): ``git clone git@github.com:Neluxx/enviro-hub.git``

2. Navigate to the project directory: ``cd enviro-hub``

3. Run application setup task: ``make app-setup``

4. Start DDEV to run the application: ``ddev start``

## Releasing

See the [release guide](docs/Releasing.md).

## Deployment

See the [deployment guide](docs/Deployment.md).

## Contributions

Have ideas or found a bug? Contributions are welcome! Feel free to fork the project and submit pull requests.

## API Reference

#### Submit sensor data to the hub

```http
  POST /api/data
```

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `temperature` | `int` | **Required**. The temperature in degrees Celsius. |
| `humidity` | `int` | **Required**. The relative humidity in percent. |
| `pressure` | `int` | **Required**. The atmospheric pressure in hPa. |
| `co2` | `int` | **Required**. The CO₂ level in ppm. |
| `created` | `datetime` | **Required**. The time of the measurement. |

## License

This project is licensed under the [Apache License 2.0](LICENSE).

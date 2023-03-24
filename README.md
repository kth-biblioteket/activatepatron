# KTH Bibliotekets tjänst för att aktivera ett konto i Alma

## Installing / Getting started

```shell
git clone repo
docker build
docker compose up -d
```

### Initial Configuration

Skapa environment fil med innehåll enligt nedan

#### Environment file
```txt

```

## Developing

### Building

```shell
git push origin main / git push origin ref
```

### Deploying / Publishing

En github action finns som skapar en ny image vid push till ref/main.

```shell
docker compose down
docker compose pull
docker compose up -d
```

## Features

Aktiverar Alma-konto för studenter med KTH-konto
*

## Contributing

## Links

## Licensing






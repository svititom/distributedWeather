# Distributed Weather Project

## 1. Introduction

This project aims to aggregate weather data (currently *temperature, Humiditiy and atmospheric pressure*) from individuals around the world, and provide this data for free to everyone. The idea is that everyone has a small portable sensor band, which would allow gathering large amounts of data, and this could be used, for example, to enhance weather forecasts.

## 2. History

A few years ago, smartphones started being equipped with various sensors including temperature and other environmental sensors. At that time, it was forecast that soon the majority of phones, and by extension the world population, would have a sensor station in their pockets. Since phones were already well connected to the internet it was easily concievable that the data from these phones could be shared and finally the data could be used to enhance weather forcasts.

Unfortunately, these sensors did not proliferate, and most phones still only have positional sensors (e.g. accelerometer, gyroscope, etc.), while other sensors are almost exclusively, if at all, in high end phones. I have not found a project or application that would be aggregating this data, which is why I have decided to start this project.

Today, IoT (Internet of Things) is a big buzzword, but it means that it is posible to create hardware systems connected to internet very easily. ?? Do we even need this paragrahp??
//started off as smart watch idea?

## 3. Band Hardware/Software

//Waiting for all hardware to arrive

- [ ] Bluetooth connection
- [ ] Interfacing with BME280
- [ ] Interfacing with battery
- [ ] Sending data over bluetooth
- [ ] Sleeping to conserve battery
- [ ] Battery consumption analysis

### Environmental Sensor

After doing some research, it seems that the Bosch BME280 is an excellent candidate. It has all 3 sensors - temperature, humidity and atm. pressure and according to some tests conducted by the hacker/maker community, it seems to perform the best compared to other sensors in the < $10 price range. 

It has *SPI* and *I2C* and operates at 3.3V which makes it easy to integrate. 

//todo link to reviews/tests

### Connectivity/Processor

The main question for connectivity is how do we atually want to use the device. Do we want a logger which stays in one place, a sort of smart band/watch which travels with the user but the logging and location is provided by the smartphone, or a logger which is in ones bag and has the possibility that the phone won't be available.

Each of these cases requires a different setup, for the first an SD card and maybe WiFi or Bluetooth would be sufficient, for the smart band, Bluetooth would be the best, as it is low power and allows one to connect to WiFi at the same time (having to disconnect from your wifi just to download data is a hug inconvenience for the user), and the last scenario might need an additional GPS module.

Most of the requirements seem to be satisfied by the Espressive ESP32 WiFi + Bluetooth SoC. It contains two ARM processors, WiFi and Bluetooth and a plethora of connectivity, which means that it can do all the data processing and sensor/external interfacing, provide most of the connectivity needed (it does not have a USB, but it does have support for an SD card if wired) and it allows for future expandability if needed.

Depending on the requirements, it would be possible to provide a secondary chip to provide USB interfacing (I believe only a USB to serial chip is needed).

### Power/battery

To allow easy production in the beginning, the system would be charged via a usb mini/micro/c port as these can be soldered and are available off the shelf.

I have no experience with adding batteries to projects, so I have orderd generic battery charging circuits and some 150mAh batteries, with which I'll try to find the best solution experimentally. Hopefully this won't be rocket science.

## 4. Software (Non band client/server)

### Data format


Since there are multiple measurements, and various sensors could be used, most probably the following set of data would make it easy to recognize:

* Value //array of data?
* Unit (short if possible, i.e. Pascal -> Pa, Celsius -> C, otherwise full), with standard SI prefixes (milli Ampere -> mA, MegaByte -> MB, etc.))
* Accuracy (In same units as Value, or as percantage of value with % at the end)
* Comment *Optional* (Could have description of unit or something else)
* Sensor *Optional*
* Timestamp *Optional*

e.g. 
* 25, C, 0.5, -, BME280, - -> 25°C, +- 0.5°C, no comment, sensor is BME280, no timestamp or
* 80, RH, 5%, -, DHT22, -  -> 80% Relative Humidity, +- 4%RH (5% of 80), no comment, sensor is DHT22, no timestamp

I will be experimenting what is the impact of the message size on battery time. If negligible, we can leave it nice and verbose, otherwise we can cut it down. (Current expectation is that it will be negligible).

### Server side

For now a single server with a database (SQL? NOSQL? __Need to ask databasists__), simple presentation frontend and REST API for communication should be sufficent.

__How to keep data?__
As they come with all info?
Use location to sort into 'location bins' and remove accurate location?

- [ ] Frontend Presentation (Home, About, etc.)
- [ ] Frontend data access 
- [ ] Frontend/Backend API (POST?)
- [ ] Backend database


### Client side

Begin with an android app which connects via bluetooth to the sensor band, periodically checks the weather data, appends location and uploads to the server.

- [ ] Bluetooth connection
- [ ] Getting location
- [ ] Connecting to web server
- [ ] Uploading data

## 0. About
Bc. Tomas Svitil,
Department of Sensors and Instrumentation, Faculty of Electrical Engineering, Czech Technical University,
2016

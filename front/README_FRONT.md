# **Peeramid front documentation**

## Development server

#### Installation

  1. Go to the "front" directory:
  
      ````
      cd front
      ````
  
  2. Install **Node.js** from the [official website](https://nodejs.org/en/).
  
  3. (Create a script to create the environments variable or implement one in the npm install script)
  
  4. To install the packages, run:
     
     ````
     npm install
     ````

#### Usage

  1. If new packages have been added or upgraded to a new version, re run:
     
     ````
     npm install
     ````

  2. Edit the environnment file:

      Open the "environment" file in the "src/environments" folder, then replace 'api_url' and 'upload_url'.
      ````
      api_url: '/* your path to access the api */',
      upload_url: '/* your path to access uploaded files */'
      ````

  3. To launch the development server, run:
  
      ````
      ng serve
      ````

  4. To acces the platform, navigate to [http://localhost:4200/](http://localhost:4200/).

     *Hot reloading will reload the page on every change in the source files.*

## Production server

Run `ng build` to build the project. The build artifacts will be stored in the `dist/` directory. Use the `-prod` flag
for a production build.

###### ask Martin to describe the server build method

## Project specifications

This project is powered by **[Angular 4.3.0](https://angular.io/)**.

This project is build with **[Angular CLI](https://github.com/angular/angular-cli/blob/master/README.md)**.

To get more help on the CLI, run:
````
ng help
````

#

[![SOS Futur](../sosf_logo.png)](https://www.sos-futur.fr/)
###### powered by [SOS Futur](https://www.sos-futur.fr/)
# Beernique - Sample Beers...with Couchbase!

Beernique is a sample PHP application using the [OpenBeerDB.com](http://openbeerdb.com/)
dataset (which we ship with Couchbase 2.0) and our [PHP SDK](http://couchbase.com/develop/php/current).

Beernique lets you track what beers you've sampled as you sample them!

## Install

  0. cd /to/your/htdocs (or equivalent)
  1. git clone
     https://github.com/couchbaselabs/couchbase-beers.git
  1. git submodule init
  1. git submodule update
  1. [get a copy of Couchbase Server
1.8.0](http://www.couchbase.com/download) (or greater).
  2. http://localhost:8091/
  3. Setup Couchbase Server the way you'd like
  4. Make a beer-sample bucket
  5. Do the following where you cloned this repo:

     $ php import.php

  6. Visit http://localhost/couchbase-beers
     (or wherever you put the repo)
  7. Enjoy!

## Open Source License

Couchbase Beers is released under the MIT public license.

======================
Pomm-project Foundation
=======================

.. contents::

Overview
--------

Foundation es una estructura de base de datos PHP PostgreSQL ligera, rápida y versátil. Se puede usar solo (para reemplazar un DBAL por ejemplo) o para construir un `administrador de modelos`_. más complejo. Es el primer cálculo del `proyecto Pomm`_ versión 2.
..  _`Pomm project`: http://www.pomm-project.org
..  _`model manager`: https://github.com/pomm-project/ModelManager

Foundation gestiona la relación entre una *conexión* de base de datos y *clientes* a través de *sesiones*. Consiste en varias clases para configurar, abrir, tratar y cerrar *sesiones*.
- ``Pomm`` es la clase de servicio, registra *constructores de sesión* y *sesiones* generadas en caché..
- ``SessionBuilder`` configura y construye *sesiones*.
- ``Session`` mantiene *clientes* y *poolers* y la *conexión*.
- ``Client`` clase abstracta que implementa la ``interfaz del cliente``. Las instancias son *clientes* de la *sesión*. 
- ``ClientPooler`` clase abstracta que implementa la ``Interfaz del Cliente Pooler``. Gestionan *clientes* en *sesiones*.

Esta complejidad a primera vista está oculta. Si se quiere abrir una conexión, enviar una consulta y obtener resultados convertidos, es tan simple como:
.. code:: php

    <?php
    //…

    $pomm = new Pomm(['my_database' => ['dsn' => 'pgsql://user:pass@host:port/db_name']]);

    $result = $pomm['my_database']  // ← note 1
        ->getQueryManager()         // ← note 2
        ->query('select * from my_table where my_field = $*', ['some value'])
        ;                           // ↑ note 3

    if ($result->isEmpty()) {
        printf("There are no results with the given parameter.\n");
    } else {
        foreach ($result as $row) { // ← note 4
            printf(
                "field1 = '%s', field2 = '%s'.\n",
                $row['field1'],     // ← note 5
                $row['field2'] === true ? 'OK' : 'NO'
            );
        }
    }

:note 1:
    Esto devuelve una *sesión* utilizando un *constructor de sesión*.

:nota 2:
    Esto devuelve el administrador de consulta predeterminado ``query_manager`` del cliente usando el ``QueryManagerPooler``.

:nota 3:
    Emita la consulta parametrizada y devuelva un ``ConvertedResultIterator``.

:nota 4:
    Recorre el resultado como una matriz y extrae filas.

:nota 5:    
    Resultado del campo de acceso. Esos resultados son covertidos a un tipo equivalente de PHP (ver `Converter pooler`_).


Servicio Pomm
------------

El servicio Pomm es una interfaz para declarar y crear *sesiones* fácilmente a través de los *constructores de sesiones*.

Usar constructores de sesión
~~~~~~~~~~~~~~~~~~~~~~

Es posible declarar constructores de sesión utilizando el constructor de clase ``Pomm`` o el método ``addBuilder``:
.. code:: php

    <?php

    $pomm = new Pomm(['first_db' => ['dsn' => 'pgsql://user:pass@host/first_db']]);
    $pomm->addBuilder('second_db', new MySessionBuilder(['dsn' => 'pgsql://user:pass@host/second_db']));

A menudo es más práctico declarar la configuración de todas las *sesiones* desde el contructor directament, incluso si el constructor es una clase personalizada:
.. code:: php

    <?php

    $pomm = new Pomm(
        [
            'first_db' =>
                [
                    'dsn' =>  'pgsql://user:pass@host/first_db'
                ],
            'second_db' =>
                [
                    'dsn' => 'pgsql://user:pass@host/second_db',
                    'class:session_builder' => '\Project\MySessionBuilder',
                    'pomm:default' => true,
                ]
        ]
    );

Cada constructor de sesión tiene un nombre. Este nombre es importante, representa una configuración y no está acoplado con el DSN. Esto es particularmente útil cuando una aplicación tiene que cambiar de una base de datos a otra con la misma configuración.

Sesión de depósito
~~~~~~~~~~~~~~~~~

La manera más fácil de obtener una sesión del servicio es usar la implementación de ``ArrayAccess``:

.. code:: php

    <?php

    $session = $pomm['first_db'];

    // this is strictly equivalent to

    $session = $pomm->getSession('first_db');

El método ``getSession($name)`` comprueba si una *sesión* ya ha sido creada utilizando este creador de sesión. En caso afirmativo, se retorna; de lo contrario, se crea una nueva utilizando ``createSession($name)``. Este último método crea una nueva sesión cada vez que es llamada. Esto implica que se usará una nueva conexión a la base de datos.

Sesiones Predeterminadas
~~~~~~~~~~~~~~~~

A veces los nombres de sesión no son tan importantes (especialmente si hay solo una sesión), en este caso es posible utilizar el mecanismo de sesión predeterminado de Pomm. Utilizará la primera declarada:
.. code:: php

    <?php

    $pomm = new Pomm(
        [
            'first_db' =>
                [
                    'dsn' =>  'pgsql://user:pass@host/first_db'
                ],
        ]
    );

    $session = $pomm->getDefaultSession(); // return a `first_db` session

Esto se aplica aún cuando se declaran varios constructores de sesión. Todavía es posible declarar explícitamente que un constructor de sesión es el predeterminado estableciendo la configuración de  `pomm::default`` como verdadero.

Configuración dependiente del contexto
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Los creadores de sesiones configuran la sesión pero en algunos casos las opciones de configuración pueden depender del contexto, como las opciones de desarrollo o de producción. Este tipo de configuración ocurre directamente en el servicio Pomm que pasa funciones anónimas:
.. code:: php

    <?php
    // …
    $pomm->addPostConfiguration('first_db', function($session) { /* … */ });

Cuando la sesión es creada, las funciones de post-configuración son iniciadas y la sesión es devuelta.

Administración de constructores de sesión
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Pomm proporciona varios métodos para administrar los constructores de sesión:

- ``addBuilder($builder_name, VanillaSessionBuilder $builder)``
- ``hasBuilder($name)``
- ``removeBuilder($name)``
- ``getBuilder($name)``
- ``getSessionBuilders()``


Constructor de Sesiones

---------------

Los *creadores de sesión* están destinados a configurar e instanciar *sesione*s. Es posible usarlos por su cuenta sin el *servicio* ``Pomm``
.. code:: php

    use PommProject\Foundation\Session\SessionBuilder;

    $session = (new SessionBuilder(['dsn' => 'pgsql://user:pass@host/db_name']))
        ->buildSession()
        ;

El constructor de sesión mostrado arriba crea sesiones sin poolers registrados. Foundation proporciona un constructor funcional con todos los poolers registrados y una clase de sesión dedicada:
.. code:: php

    use PommProject\Foundation\SessionBuilder; // ← different session builder

    $session = (new SessionBuilder(['dsn' => 'pgsql://user:pass@host/db_name']))
        ->buildSession()
        ;

Configuración:
~~~~~~~~~~~~~

Hay varias formas de establecer la configuración:

.. code:: php

    <?php

    $session_builder = new SessionBuilder(
        [
            'dsn'   => 'pgsql://user:pass@host:port/db_name',
            'param' => 'value',
        ]
    );
    $session_builder->addParameter('my_parameter', 'my_value');

De forma general, la clase ``SessionBuilder`` esta hecha para ser extendida por un clase *constructora de sesión* dedicada del proyecto. Entonces es posible sobrecargar el método ``getDefaultConfiguration()``. Esto mantiene la clase configurable como una configuración personalizada predeterminada.

Opciones de configuración
~~~~~~~~~~~~~~~~~~~~~

El ``dsn``es el unico parametro obligatorio esperado por el constructor pero puede ser pasados más parametros:
- ``connection:configuration`` (array)
- ``dsn`` (string) mandatory
- ``class:session`` (string) default:  ``\PommProject\Foundation\Session\Session``

El parametro ``connection:configuration`` contiene un hasmap de la configuraciones de postgresql ( ver `documentación de postgresql <http://www.postgresql.org/docs/9.1/static/runtime-config-client.html>`_). La configuraciones por defecto son las siguientes:

- ``bytea_output``                (string) default: ``hex``
- ``intervalstyle``               (string) default: ``ISO_8601``
- ``datestyle``                   (string) default: ``ISO``
- ``standard_conforming_strings`` (string) default: ``true``

**dsn** es el unico parametro obligatorios, es es utilizado para conectar a la base de datos de Postgresql. La sintaxis es la siguiente::
    pgsql://user:password@host:port/db_name

Ejemplos::

    pgsql://db_user/db_name
    pgsql://db_user:p4sS@192.168.1.101/db_name
    pgsql://db_user:p4sS@192.168.1.101:5433/db_name
    pgsql://db_user@!/var/run/postgres!:5433/db_name


:Nota:
    La libreria de Pgsql es sensible a las variables de entorno ``PGHOST`` ``PGPORT``(ver `la documentación  <http://www.postgresql.org/docs/9.1/static/libpq-envars.html>`_).Cuando se esta usando PHP desde la linea de comando (o el built-in en el servidor web), estas variables tendran un impacto si no son anuladas por algunos de los parametros DSN.
:Nota:
    La parte del host necesita ser una ruta en el archivo local de sistema rodeado por el caracter ``!`. Cuando este sea el caso, el socket de Unix presente en el directorio dado es usado para conectar a la base de datos.

Personalización de sesión
~~~~~~~~~~~~~~~~~~~~~

La clase de ``constructor de sesión`` es hecha para ser extendida. EL paquete de Foundation incidentalmente propone dos *constructores de sesiones*:
- ``PommProject\Foundation\Session\SessionBuilder`` blank session builder.
- ``PommProject\Foundation\SessionBuilder`` builder with Foundation *clients* and *poolers* loaded and configured.

Esto es alentado para crear un proyecto-dedicado *constructor de sesión* que extiende una de estas clases. Varios metodos estan disponibles para cambiar el comportamiento de *constructor de sesión* 

:``getDefaultConfiguration``:
    Soobrescribe por defecto la configuración. La configuración del nucleo por defecto es el parametro `connection:configuration`. Ten cuidado esto afectara el convertidor del sistema por defecto si es descartado.

:``preConfigure()``:
    Cambiar la configuración solo antes de que una sesión sea instanciada.

:``postConfigure($session)``:
    Lugar donde *session poolers* y *clientes* son registrados en una nueva marca de la nueva *sesión*.

:``createSession()``:
    Si una sesión de una clase personalizada es para ser instanciada.

:``createClientHolder()``:
    Si una *session holder* personalizado es para ser usada dentro de la *sesión*.


:``initializeConverterHolder()``:
    Personalice el soporte del convertidor. Recuerde todas las sesiones creadas por el constructor tendrán este soporte convertidorsea cual sea su DSN


:``createConnection()``:
    Como crear una instancia de conexión basada en la configuración.




Soporte del convertidor
~~~~~~~~~~~~~~~~

El *soporte del convertidor* es un ajuste de configuración especial. Este soporte contiene todos los convertidores y se clona cuando se transfiere como parámetro al `convertidor pooler`_. Una preconfiguración personalizada del *soporte del convertidor* se puede transferir como parámetro al *contructor de sesión*:

.. code:: php

    <?php

    $session_builder = new SessionBuilder(
        ['dsn' => 'pgsql://user:pass@host:port/db_name'],
        new MyConverterHolder()
        );

El método ``initializeConverterHolder()`` se usa internamente para registrar conversores predeterminados de tipo PostgreSQL, utilízalo para agregar tus propios conversores predeterminados. La instancia de ``ConverterHolder`` se transfiere como referencia. Recuerde, este soporte del convertidor se utilizará para *todas* las sesiones creadas por el constructor sea cual sea su DSN. Si se va a registrar un convertidor específico de la base de datos, el mejor lugar para ello podría ser el método ``postConfigure``, que trata directamente con `convertidor pooler`_.

Sesión
-------

La sesión es pieza clave del paquete de Foundation. Proporciona una API de *conexión* a los *clientes*. Para poder hacer esto, los *clientes* deben registrarse a la *sesión* usando el método ``registerClient(ClientInterface)``.A cambio, se inyecta en el *cliente* utilizando el método de ``initialize(Session)`` (see `Client`_). A partir de esto, el *cliente* puede usar la *conexión* y otros *clientes*.


Se accede a los *clientes* utilizando el método ``getClient($type, $identifier)``. Si ningún cliente coincide con el tipo e identificador correspondiente, se devuelve como``null`. Esto puede ser un problema porque el Cliente debe ser instanciado y registrado en la Sesión. Este es el papel de los *clientes poolers* (aka poolers). *Poolers* son, en cierto modo, administradores de *clientes* para un tipo determinado. No todos los tipos necesitan un *pooler*, por ejemplo, los clientes de tipo ``fijo` gestionan las estructuras y los datos de prueba de la base de datos. Están aquí para crear tablas y tipos necesarios para las pruebas en el inicio y para soltarlos al apagar.Alternativamente, La consulta `prepared query pooler`_  toma la consulta SQL como identificador del cliente. Si la consulta dada ya se ha realizado, se vuelve a utilizar. De lo contrario, una nueva preparación es declarada y luego ejecutada.Cuando la *conexión* se cae, todas las declaraciones son desasignadas.



Algunos *clientes* pueden usar *clientes* de diferentes tipos utilizando sus respectivos *poolers. Por ejemplo, el cliente ``PreparedQueryManager`` usa el administrador de consultas pooler y luego el `convertidor pooler`_.

There are several ways to access *clients* and *poolers* using the *session*:

:``getClient($type, $identifier)``:     return the asked *client* if it exists, null otherwise.
:``getClientUsingPooler($type, $identifier)``:  ask for a *client* using a *client pooler*.

There is a shortcut for the last method:

.. code:: php

    <?php

    $client = $session->getType($identifier);

    // strictly equivalent to
    $client = $session->getClientUsingPooler($type, $identifier);

    // which is the same as
    $client = $session
        ->getPoolerForType($type)
        ->getClient($identifier)
        ;

Client
------

A *client* is a bit of work with the database. They should be as simple as possible and as reliable as possible. They work together through *session* and *poolers*.

All *clients* must implement ``ClientInterface``. Because a part of this implementation is always the same, it is possible to either extend ``PommProject\Foundation\Client\Client`` or to use ``PommProject\Foundation\Client\ClientTrait``. (The ``Client`` abstract class just uses the ``ClientTrait``). The interface defines 4 methods to be implemented:

:``getClientType()``:   Return client type, not implemented in ``ClientTrait``.
:``getClientIdentifier()``:  Return client identifier, not implemented in ``ClientTrait``.
:``initialize(Session)``:   When the client is registered by the session, the session injects itself in the *client* using this method.
:``shutdown()``:    If things are to be done before connection is going down.

Client pooler
-------------

A *client pooler* manages *clients* of a given type. Its role is to return a client or throw an exception otherwise.

All *client poolers* must implement ``ClientPoolerInterface``. It is possible to easily implement this either by extending ``ClientPooler`` or using ``ClientPoolerTrait`` (the abstract class uses the trait). The interface defines three methods:

:``getPoolerType()``:   Return the type of *clients* managed by this *pooler*, not implemented in ``ClientPoolerTrait``.
:``register(Session)``:  When the *pooler* is registered to the session, the session injects itself in the *pooler* using this method.
:``getClient($identifier)``:    Method called to fetch a *client* using this *pooler*.

Because most *poolers* behave the same way, the ``ClientPoolerTrait`` add methods to work like the following. When a *client* is requested:

#. Retrieve the client from the *session*’s *client holder*.
#. If null is returned, it launches ``createClient($identifier)`` method.
#. If the *client* cannot be created, an exception must be thrown.
#. Return the *client*.

Default client poolers
----------------------

Here is a comprehensive list of the *poolers* registered by default with ``PommProject\Foundation\SessionBuilder``.

Converter pooler
~~~~~~~~~~~~~~~~

:Type:  converter

Responsible of proposing converter *clients*. If a client is not found, it checks in the *converter holder* if the given type has a converter. If yes, it wraps the *converter* in a ``ConverterClient`` and registers it to the session. There are as many ``ConverterClient`` as registered types but they can share the same *converter* instances.

This way, it is possible to add custom converters or converters for database specific types like composite types. The best place to do that is in a `Session builder`_’s ``postConfigure(Session)`` method:

.. code:: php

    <?php
    //…
    function postConfigure(Session $session)
    {
        $session
            ->getPoolerForType('converter')
            ->getConverterHolder()
            ->addTypeToConverter('my_schema.latlong', 'Point') // ← convert a domain of point
            ->registerConverter('Hstore', new PgHstore(), ['public.hstore']) // ← register Hstore converter
            ;
    }

Even though the converters coming with Foundation cover a broad range of PostgreSQL’s types, it is possible to write custom converters as long as they implement ``ConverterInterface``. Be aware that the format of the data coming from Postgres may be configuration dependent (dates, money, number etc.). Default converters fit the default configuration set in the `Session builder`_.


Inspector pooler
~~~~~~~~~~~~~~~~

:Type:  inspector

This *pooler* calls the ``PommProject\Foundation\Inspector\Inspector`` *client* by default. It is possible to specify another *client* class as identifier, the *pooler* will try to instantiate it.

The inspector proposes methods to get information about database structure (schemas, tables, fields etc.).

Listener pooler
~~~~~~~~~~~~~~~

:Type:  listener

A ``Listener`` is a class that can hold anonymous functions that are triggered when the listener receives a notification with the listener’s name.

Foundation owns a basic event dispatcher mechanism.

.. code:: php

    <?php
    //…

    $session
        ->getListener('my_event')
        ->attachAction(function($event_name, $data, $session) { // do something })
        ;

To trigger the attached functions, the listener *pooler* proposes a ``notify(array, mixed)`` method. The first argument is an array of event names and the second is the data payload to be sent. Albeit simple, this mechanism is powerful since all attached functions have access to the session hence all the *poolers*.

There is also a method to notify all clients:

.. code:: php

    <?php
    //…

    $session
        ->getPoolerForType('listener')
        ->notify('*', $some_data)
        ;

Observer pooler
~~~~~~~~~~~~~~~

:Type:  observer

Observer *pooler* aims at leveraging the ``LISTEN/NOTIFY`` mechanism in PostgreSQL. An observer *client* can be used to listen to PostgreSQL events sent with the ``NOTIFY`` SQL command. It is possible to ask the observer either to send back the event payload if any or to throw a ``NotificationException`` when a notification is caught.

Prepared query pooler
~~~~~~~~~~~~~~~~~~~~~

:Type: prepared_query

This *pooler* prepares statements if they do not already exist and executes them with parameters:

.. code:: php

    <?php
    //…
    $session
        ->getPreparedQuery('select * from my_table where some_field = $*')
        ->execute(['some_content']
        ;

It returns a ``ResultHandler`` instance with raw results. (see `Query manager pooler`_).

Query manager pooler
~~~~~~~~~~~~~~~~~~~~

:Type:  query_manager

The query manager *pooler* returns a traversable iterator (see `result iterators`_) on converted results. The default *client* is a simple parametrized query but Foundation also comes with a prepared query manager:

.. code:: php

    <?php
    //…
    $result = $session
        ->getQueryManager('\PommProject\Foundation\PreparedQuery\PreparedQueryManager')
        ->query('select * from my_table where some_field = $*', ['some_content'])
        ;

If no client class is provided, the default ``PommProject\Foundation\QueryManager\SimpleQueryManager`` is used. By default, parameters are passed as-is to the driver. It is somehow possible to explicitely declare the type of some or all the parameters in the query. The query manager will then use the `converter pooler`_ to convert them in a Postgresql format.

.. code:: php

    <?php
    //…
    use PommProject\Foundation\Converter\Type\Point;

    // Are there open bike stations around me ?
    $result = $session
        ->getQueryManager()
        ->query(
            "select station_id, public_name, available_slots
            from bike_station b
            where b.coordinates <@ circle($*::point, $*) and b.status = any($*::varchar[])",
            [new Point($position), $radius, ['full', 'reduced']]
        );

The example above shows how to pass simple but also complex parameters like geomtric types and arrays.

Adding custom poolers and clients
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Poolers and clients must implement ``ClientPoolerInterface`` and ``ClientInterface`` respectively. To make this process easier, it is somehow possible to extend the ``ClientPooler`` that uses the ``ClientPoolerTrait`` (or to use this trait directly). It will make custom class to work in a given way when a client is requested:

1.  If the client exists, it is fetched from the pool and returned (``getClient($identifier)`` and ``getClientFromPool($identifier)``).
2.  If the client does not exist, it is created, registered to the session and returned (``createClient($identifier)``)

The methods above can of course be overloaded. The only methods let to the developer are:

:``getPoolerType()``: That returns the client type handled by this pooler.
:``createClient($identifier)``: How to create a client of this type.


Result iterators
----------------

basic usage
~~~~~~~~~~~

There are two kinds of iterators that can be used with Pomm:

:``ResultIterator``:
    Implements all the methods for ``\SeekableIterator``, ``\Countable`` and ``\JsonSerializable`` interfaces. It just returns the raw results as they are fetched from the driver.

:``ConvertedResultIterator``:
    It extends ``ResultIterator`` but uses the converter pooler (see `Converter pooler`_) to convert data to a PHP representation. This is the one used by default by the query managers.

These iterators do fetch data lazily, this means rows are fetched on demand. This presents significant advantages in terms of performances and memory consumption. Furthermore, Pomm’s iterators are scrollable which means they are seek-able and they can be traversed several times.

.. code:: php

    <?php
    //…
    $results = $session
        ->getQueryManager()
        ->query("select generate_series(1, $*::int4) as a_number", [10])
        // ↑ generates from 1 to 10 (passed as parameter)
        ;

    $results->get(0); // returns ["a_number" => 1];
    $results->get(9); // returns ["a_number" => 10];

    try {
        $results->get(10);
    } catch (\OutOfBoundsException $e) {
        // index starts from 0
    }

    foreach ($results as $index => $result) { // traverse results
        printf("Result %02d => %d\n", $index, $result['a_number']);
    }

Expanding iterators
~~~~~~~~~~~~~~~~~~~

Even though iterators are lazy, it is possible to fetch all the results in one step and store them in memory.

:``extract()``:
    Simple dump an array of rows like ``PDO::fetchAll()``.

:``slice($column_name)``:
    return a one dimension array of the values stored in this result’s column.

Since the iterators implement the ``\JsonSerializable`` interface it is possible to simply export them in the JSON format by calling ``json_encode($iterator)``.

Other methods
~~~~~~~~~~~~~

Result iterators also propose handy methods 

:``current()``:
    Return the row pointed by the current cursor’s position in the result. This is used most of the time to extract a row in single result query like ``SELECT count(*) FROM …``.

:``count()``:
    Returns the number of rows of the result. Required by the ``\Countable`` interface.

:``isEmpty()``:
    Returns if the result set is empty (no results) or not.

:``isFirst``:
    If the result is not empty, it returns true if the iterator points on the first result. This is sometimes interesting if the iterator is traversed in the view (html templates or so) to add table informations prior to the first line.

:``isLast()``:
    If the result is not empty, it returns true if the iterator points on the last result. (see ``isFirst``).

:``isOdd()``:
    Returns true if the current cursor position is not divisible by two. Handy to easily change the background color of a result set a row on two.

:``isEven()``:
    Opposite of ``isOdd()``.

Where: the condition builder
----------------------------

Basic usage
~~~~~~~~~~~

Pomm comes with a dedicated class to build SQL conditions dynamically: the ``Where`` class. It use is pretty straightforward:

.. code:: php

    <?php
    use PommProject\Foundation\Where;
    //…
    $sql = "SELECT * FROM a_table WHERE :condition"
    $where = new Where();
    strtr($sql, [':condition' => $where]); // … WHERE true

    $where->andWhere('a is null');
    strtr($sql, [':condition' => $where]); // … WHERE a is null

    $where->andWhere('b');
    strtr($sql, [':condition' => $where]); // … WHERE a is null AND b

    $where->orWhere('not c');
    strtr($sql, [':condition' => $where]); // … WHERE (a is null AND b) OR not c

The example above shows how it deals with operator precedence. For convenience, it is possible to directly pass a ``Where`` class as argument to the ``andWhere`` and ``orWhere`` methods:

.. code:: php

    $where = new Where('a is not null');
    $where->orWhere(Where::create('b')->andWhere('not c'));
    // a is not null OR (b AND not c)

Dealing with parameters
~~~~~~~~~~~~~~~~~~~~~~~

Most of the time, condition clauses do rely on external parameters. The ``Where`` clause allows them to be attached to the condition they belong to so they can be passed in the right order to a ``query`` method:

.. code:: php

    $where = Where::create("status = $*", [$parameter1])
        ->andWhere("amount > $*", [$parameter2])
        ;

    $sql = strtr(
        "select … from a_table where :condition",
        [
            ':condition' => $where,
        ]
    );

    $results = $session
        ->getQueryManager()
        ->query($sql, $where->getValues())
        ;

There are special clauses to handle the SQL ``IN`` operator:

.. code:: php

    $where = Where::createWhereIn("status",
        [
            $parameter1,
            $parameter2,
            …,
            $parameterN,
        ]
    );
    // status IN ($*, $*, …, $*)

There is obviously a complementary ``createWhereNotIn`` method.


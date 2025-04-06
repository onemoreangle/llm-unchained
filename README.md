# llm-unchained

PHP library to incorporate LLMs (Large Language Models) into your applications.

> **Warning**  
> This project is unstable and under development

## Origin
This project originates from a direct fork/clone of the [**llm-chain**](https://github.com/php-llm/llm-chain) project. The foundational work by the authors is gratefully acknowledged and appreciated.

`llm-unchained` was created primarily because the process of adding features for real-world use cases using `llm-chain` was found to be significantly hampered by overly restrictive design constraints (architectural as well as code style). These choices consistently slowed development progress. `llm-unchained` seeks to provide a balance that allows developers (myself included) to prototype more quickly and to provide the needed functionality and integrations more rapidly and with less friction.

## Requirements

* PHP 8.2 or higher

## Installation

The recommended way to install llm-unchained is through [composer](http://getcomposer.org/):

```bash
composer require onemoreangle/llm-unchained
```

## Examples

See [examples](examples) folder to run example implementations using this library.
Depending on the example you need to export different environment variables
for API keys or deployment configurations or create a `.env.local` based on `.env` file.

## Documentation
For documentation in addition to the examples, please consult the documentation of the original [**llm-chain**](https://github.com/php-llm/llm-chain) for now, but be aware that the APIs, classes, and methods may differ from the original `llm-chain` project.

> **Note**
> The examples within this repository should always be up to date with the current state of the codebase, so they are the best source of truth on how to use the library.

## Contributing
Currently, this project is maintained by the author but collaborations are definitely welcome, so please feel free to open issues or pull requests.

## License
This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

### Fixture Licenses (from original project)

For testing multi-modal features, the repository contains binary media content, with the following owners and licenses:

* `tests/Fixture/image.jpg`: Chris F., Creative Commons, see [pexels.com](https://www.pexels.com/photo/blauer-und-gruner-elefant-mit-licht-1680755/)
* `tests/Fixture/audio.mp3`: davidbain, Creative Commons, see [freesound.org](https://freesound.org/people/davidbain/sounds/136777/)

.PHONY: run build

build:
	docker build -t multi-thread-manager .

run:
	docker run --rm --interactive --tty --volume $(PWD):/app -w /app multi-thread-manager bash

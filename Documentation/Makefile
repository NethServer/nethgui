#
# Documentation Makefile
#

RSTFILES := $(shell find . -name '*.rst')
HTMLFILES := $(addsuffix .html, $(basename ${RSTFILES}))

RST2HTML=rst2html.py --stylesheet-path=css/style.css --link-stylesheet

%.html: %.rst
	${RST2HTML} $< $@

.PHONY: clean all

all: ${HTMLFILES}

clean:
	rm -f ${HTMLFILES}
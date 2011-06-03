#
# Documentation Makefile
#

RSTFILES := $(shell find . -name '*.rst')
HTMLFILES := $(addsuffix .html, $(basename ${RSTFILES}))

RST2HTML=rst2html.py --stylesheet-path=css/style.css --link-stylesheet

PDEPEND_OUTPUT=Metrics/pyramid.svg Metrics/jdepend.svg Metrics/summary.xml

%.html: %.rst
	${RST2HTML} $< $@

.PHONY: clean all api

all: ${HTMLFILES} ${PDEPEND_OUTPUT} 

api: 
	phpdoc -dn NethGui -ti NethGui -d ../NethGui -i *Template* -t Api

clean:
	rm -f ${HTMLFILES} ${PDEPEND_OUTPUT}
	rm -rf Api/*

${PDEPEND_OUTPUT}:
	pdepend \
	  --summary-xml=Metrics/summary.xml  \
	  --jdepend-chart=Metrics/jdepend.svg  \
	  --overview-pyramid=Metrics/pyramid.svg  \
	  --ignore=../NethGui/Language/ \
	  ../NethGui/
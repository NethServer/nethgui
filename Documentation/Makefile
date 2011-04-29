#
# Documentation Makefile
#

RSTFILES := $(shell find . -name '*.rst')
HTMLFILES := $(addsuffix .html, $(basename ${RSTFILES}))

RST2HTML=rst2html.py --stylesheet-path=css/style.css --link-stylesheet

PDEPEND_OUTPUT=Metrics/pyramid.svg Metrics/jdepend.svg Metrics/summary.xml

%.html: %.rst
	${RST2HTML} $< $@

.PHONY: clean all

all: ${HTMLFILES} ${PDEPEND_OUTPUT}

clean:
	rm -f ${HTMLFILES} ${PDEPEND_OUTPUT}

${PDEPEND_OUTPUT}:
	pdepend \
	  --summary-xml=Metrics/summary.xml  \
	  --jdepend-chart=Metrics/jdepend.svg  \
	  --overview-pyramid=Metrics/pyramid.svg  \
	  --ignore=../NethGui/Language/,../NethGui/Module/,../NethGui/View/ \
	  ../NethGui/
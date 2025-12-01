package p5.educationalstrange.ehtmlgenerator;

import p5.educationalstrange.ematchfragmentgenerator.EMatchFragment;

public class EHtmlTableTuple implements Comparable<EHtmlTableTuple> {
	private EMatchFragment entity;
	// adapted from Faidhi and Robinson's disguise levels. Level 0: verbatim copy.
	// Level 1: whitespace modification. Level 2: comment modification. Level 3:
	// identifier name modification. Level 4: constant modification. Level 5: data
	// type modification.
	private int modificationLevel;
	// simply the number of copied chars prior copied. Take instantly from
	// EMatchFragment
	private int copiedCharLength;
	// high score refers to high concerns. Calculated as (6+1)-concernPriority *
	// copiedCharLength. This is used for sorting the concern priority
	private int importanceScore;

	public EHtmlTableTuple(EMatchFragment entity) {
		super();
		this.entity = entity;

		// set modification level
		// verbatim copy
		this.modificationLevel = 0;
		// whitespace modification
		if (entity.isWhitespaceModified())
			this.modificationLevel = 1;
		// comment modification
		if (entity.isCommentModified())
			this.modificationLevel = 2;
		// identifier name modification
		if (entity.isIdentifierNameModified())
			this.modificationLevel = 3;
		// constant modification
		if (entity.isConstantValuesModified())
			this.modificationLevel = 4;
		// data type modification
		if (entity.isDataTypeModified())
			this.modificationLevel = 5;

		// set the other attributes
		this.copiedCharLength = entity.getCopiedCharLength();
		this.importanceScore = ((6 + 1) - this.modificationLevel) * this.copiedCharLength;

	}

	@Override
	public int compareTo(EHtmlTableTuple arg0) {
		// TODO Auto-generated method stub
		return -this.getImportanceScore() + arg0.getImportanceScore();
	}

	public int getImportanceScore() {
		return importanceScore;
	}

	public void setImportanceScore(int importanceScore) {
		this.importanceScore = importanceScore;
	}

	public EMatchFragment getEntity() {
		return entity;
	}

	public void setEntity(EMatchFragment entity) {
		this.entity = entity;
	}

	public int getConcernPriority() {
		return modificationLevel;
	}

	public void setConcernPriority(int concernPriority) {
		this.modificationLevel = concernPriority;
	}

	public int getCopiedCharLength() {
		return copiedCharLength;
	}

	public void setCopiedCharLength(int copiedCharLength) {
		this.copiedCharLength = copiedCharLength;
	}

	public int getModificationLevel() {
		return modificationLevel;
	}

	public void setModificationLevel(int modificationLevel) {
		this.modificationLevel = modificationLevel;
	}
}
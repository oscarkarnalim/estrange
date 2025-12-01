package p5.educationalstrange.ematchfragmentgenerator;

import java.util.ArrayList;

import p5.educationalstrange.tuple.ETokenTuple;

public class EMatchFragment implements Comparable<EMatchFragment> {
	private ETokenTuple startToken, finishToken;
	private boolean isWhitespaceModified, isCommentModified, isIdentifierNameModified, isConstantValuesModified,
			isDataTypeModified;

	// the copied and artificially disguised code fragment
	private ArrayList<ETokenTuple> copied;
	// the number of copied chars (before disguised)
	private int copiedCharLength;
	// number of tokens before the match
	private int numOfPretokens;
	// number of tokens after the match
	private int numOfPosttokens;

	// lists the applied disguises
	private ArrayList<Integer> appliedDisguises;

	// ID for HTML result
	private String visualId;

	public EMatchFragment(ETokenTuple startToken, ETokenTuple finishToken) {
		super();
		this.startToken = startToken;
		this.finishToken = finishToken;
		this.copied = null;
	}

	public ETokenTuple getStartToken() {
		return startToken;
	}

	public void setStartToken(ETokenTuple startToken) {
		this.startToken = startToken;
	}

	public ETokenTuple getFinishToken() {
		return finishToken;
	}

	public void setFinishToken(ETokenTuple finishToken) {
		this.finishToken = finishToken;
	}

	public boolean isWhitespaceModified() {
		return isWhitespaceModified;
	}

	public void setWhitespaceModified(boolean isWhitespaceModified) {
		this.isWhitespaceModified = isWhitespaceModified;
	}

	public boolean isCommentModified() {
		return isCommentModified;
	}

	public void setCommentModified(boolean isCommentModified) {
		this.isCommentModified = isCommentModified;
	}

	public boolean isIdentifierNameModified() {
		return isIdentifierNameModified;
	}

	public void setIdentifierNameModified(boolean isIdentifierNameModified) {
		this.isIdentifierNameModified = isIdentifierNameModified;
	}

	public boolean isConstantValuesModified() {
		return isConstantValuesModified;
	}

	public void setConstantValuesModified(boolean isConstantValuesModified) {
		this.isConstantValuesModified = isConstantValuesModified;
	}

	public boolean isDataTypeModified() {
		return isDataTypeModified;
	}

	public void setDataTypeModified(boolean isDataTypeModified) {
		this.isDataTypeModified = isDataTypeModified;
	}

	public ArrayList<ETokenTuple> getCopied() {
		return copied;
	}

	public void setCopied(ArrayList<ETokenTuple> copied) {
		this.copied = copied;
	}

	public int getNumOfPretokens() {
		return numOfPretokens;
	}

	public void setNumOfPretokens(int numOfPretokens) {
		this.numOfPretokens = numOfPretokens;
	}

	public ArrayList<Integer> getAppliedDisguises() {
		return appliedDisguises;
	}

	public void setAppliedDisguises(ArrayList<Integer> appliedDisguises) {
		this.appliedDisguises = appliedDisguises;
	}

	public String getVisualId() {
		return visualId;
	}

	public void setVisualId(String visualId) {
		this.visualId = visualId;
	}

	public int getNumOfPosttokens() {
		return numOfPosttokens;
	}

	public void setNumOfPosttokens(int numOfPosttokens) {
		this.numOfPosttokens = numOfPosttokens;
	}

	public int getCopiedCharLength() {
		return copiedCharLength;
	}

	public void setCopiedCharLength(int copiedCharLength) {
		this.copiedCharLength = copiedCharLength;
	}

	@Override
	public int compareTo(EMatchFragment o) {
		// TODO Auto-generated method stub
		if (this.getStartToken().getLine() != o.getStartToken().getLine())
			return this.getStartToken().getLine() - o.getStartToken().getLine();
		else
			return this.getStartToken().getColumn() - o.getStartToken().getColumn();
	}

}
